<?php
namespace App\Service;

use App\Entity\MembreFamille;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Paiement;
use App\Entity\Inscription;
use App\Entity\HistoriquePaiement;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InscriptionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StripeClient $stripe,
        private UrlGeneratorInterface $router
    ) {}

    public function startCheckout(
        Sortie $sortie,
        User $user,
        array $membres = [],
        bool $inscrireUser = true
    ): string {
        $participants = ($inscrireUser ? 1 : 0) + count($membres);
        $placesRestantes = $sortie->getNbInscriptionMax()
            - $sortie->getParticipants()->count()
            - $sortie->getMembresFamilleInscrits()->count();
        if ($participants > $placesRestantes) {
            throw new \RuntimeException("Il ne reste que $placesRestantes place(s).");
        }

        $paiement = new Paiement();
        $paiement->setUtilisateur($user)
            ->setSortie($sortie)
            ->setParticipants($participants)
            ->setMontant($sortie->getPrix() * 100 * $participants);
        $this->em->persist($paiement);
        $this->em->flush();

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $sortie->getPrix() * 100,
                    'product_data' => ['name' => $sortie->getTitre()],
                ],
                'quantity' => $participants,
            ]],
            'mode' => 'payment',
            'success_url' => $this->router->generate('paiement_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $this->router->generate('paiement_cancel', ['sortie' => $sortie->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata'    => [
                'idsMembres'     => implode(',', array_map(fn($m) => $m->getId(), $membres)),
                'inscription_me' => $inscrireUser ? '1' : '0',
            ],
        ]);

        $paiement->setStripeSessionId($session->id);
        $this->em->flush();

        return $session->url;
    }

    public function finalizeInscription(Paiement $paiement): void
    {
        $sortie = $paiement->getSortie();
        $user    = $paiement->getUtilisateur();
        $session = $this->stripe
                        ->checkout
                        ->sessions
                        ->retrieve($paiement->getStripeSessionId());
        $metadata = $session->metadata;

        // Enregistrer les IDs pour pouvoir rembourser ultérieurement
        if (!empty($session->payment_intent)) {
            $paiement->setStripePaymentIntentId($session->payment_intent);
            // Récupérer la charge pour obtenir l'ID
            $pi = $this->stripe->paymentIntents->retrieve(
                $session->payment_intent,
                ['expand' => ['charges']]
            );
            if (!empty($pi->charges->data[0]->id)) {
                $paiement->setStripeChargeId($pi->charges->data[0]->id);
            }
            $this->em->persist($paiement);
        }
        $this->em->flush();

        // Inscription de l'utilisateur
        if (!empty($metadata->inscription_me)) {
            $insc = (new Inscription())
                ->setNom($user->getPrenom() . ' ' . $user->getNom())
                ->setUtilisateur($user)
                ->setInscritPar($user)
                ->setSortie($sortie)
                ->setPaiement($paiement);
            $this->em->persist($insc);
            $sortie->addParticipant($user);
        }

        // Inscription des membres de la famille
        $ids = array_filter(explode(',', $metadata->idsMembres));
        if ($ids) {
            $membres = $this->em->getRepository(MembreFamille::class)->findBy(['id' => $ids]);
            foreach ($membres as $m) {
                $insc = (new Inscription())
                    ->setNom($m->getPrenom() . ' ' . $m->getNom())
                    ->setMembreFamille($m)
                    ->setInscritPar($user)
                    ->setSortie($sortie)
                    ->setPaiement($paiement);
                $this->em->persist($insc);
                $sortie->addMembresFamilleInscrit($m);
            }
        }

        // Journal du paiement validé
        $log = (new HistoriquePaiement())
            ->setType('paiement_valide')
            ->setDate(new \DateTimeImmutable())
            ->setMessage(sprintf(
                "Paiement validé pour la sortie « %s » (%d participants).",
                $sortie->getTitre(),
                $paiement->getParticipants()
            ))
            ->setUtilisateur($user)
            ->setSortie($sortie);
        $this->em->persist($log);

        $this->em->flush();
    }

    public function unregisterParticipant(
        Sortie $sortie,
        User $user,
        ?MembreFamille $membre = null
    ): void {
        // Récupération de l’inscription correspondant à cette personne (user ou membre)
        if ($membre) {
            $insc = $this->em->getRepository(Inscription::class)
                ->findOneBy([
                    'sortie'       => $sortie,
                    'membreFamille'=> $membre
                ]);
        } else {
            $insc = $this->em->getRepository(Inscription::class)
                ->findOneBy([
                    'sortie'      => $sortie,
                    'utilisateur' => $user
                ]);
        }
        if (!$insc) {
            // Inscrit manuellement par l’admin : retirer simplement sans erreur ni remboursement
            if ($membre) {
                $sortie->removeMembresFamilleInscrit($membre);
            } else {
                $sortie->removeParticipant($user);
            }
            $this->em->flush();
            return;
        }
        $paiement = $insc->getPaiement();
        if (!$paiement || $paiement->getStatut() !== Paiement::STATUT_PAYE) {
            // Pas de paiement ou pas payé : supprimer l’inscription sans remboursement
            $this->em->remove($insc);
            if ($membre) {
                $sortie->removeMembresFamilleInscrit($membre);
            } else {
                $sortie->removeParticipant($user);
            }
            $this->em->flush();
            return;
        }

        // Calcul du montant par place
        $totalSeats    = $paiement->getParticipants();
        if ($totalSeats < 1) {
            throw new \RuntimeException('Impossible de calculer le montant du remboursement : aucune place payé.');
        }
        $totalAmount   = $paiement->getMontant();
        $amountPerSeat = intdiv($totalAmount, $totalSeats);

        // Détermination si le remboursement est autorisé (avant -48h du début)
        $now = new \DateTimeImmutable();
        $refundDeadline = (clone $sortie->getDate())->sub(new \DateInterval('P2D'));
        $shouldRefund = $now < $refundDeadline;

        if ($shouldRefund) {
            // Récupération du chargeId si absent
            $chargeId = $paiement->getStripeChargeId() ?: null;
            if (!$chargeId && $paiement->getStripePaymentIntentId()) {
                $pi = $this->stripe->paymentIntents->retrieve(
                    $paiement->getStripePaymentIntentId(),
                    ['expand' => ['charges']]
                );
                $chargeId = $pi->charges->data[0]->id ?? null;
            }

            // Paramètres de remboursement partiel
            $params = ['amount' => $amountPerSeat];
            if ($chargeId) {
                $params['charge'] = $chargeId;
            } else {
                $params['payment_intent'] = $paiement->getStripePaymentIntentId();
            }
            $this->stripe->refunds->create($params);

            // Mise à jour du Paiement
            $paiement
                ->setParticipants($totalSeats - 1)
                ->setMontant($totalAmount - $amountPerSeat);
            $this->em->persist($paiement);
        }

        // Supprimer l'entité Inscription correspondante
        $this->em->remove($insc);

        // Suppression de l'inscription
        if ($membre) {
            $sortie->removeMembresFamilleInscrit($membre);
        } else {
            $sortie->removeParticipant($user);
        }

        // Journal du remboursement partiel (uniquement si remboursement effectué)
        if ($shouldRefund) {
            $log = (new HistoriquePaiement())
                ->setType('remboursement_partiel')
                ->setDate(new \DateTimeImmutable())
                ->setMessage(sprintf(
                    "Remboursement partiel de %.2f € pour %s.",
                    $amountPerSeat / 100,
                    $membre ? $membre->getPrenom() : $user->getPrenom()
                ))
                ->setUtilisateur($user)
                ->setSortie($sortie);
            $this->em->persist($log);
        }

        $this->em->flush();
    }
}