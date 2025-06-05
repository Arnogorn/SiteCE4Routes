<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Sortie;
use App\Entity\HistoriquePaiement;
use App\Repository\PaiementRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Entity\MembreFamille;
use App\Entity\Inscription;
use App\Service\InscriptionService;
use Stripe\Exception\ApiErrorException;


#[Route('/paiement', name: 'paiement_')]
class PaiementController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/choisir/{id}', name: 'choisir_membres', methods: ['GET'])]
    public function choisirMembres(Sortie $sortie): Response
    {
        return $this->render('paiement/choisir_membres.html.twig', [
            'sortie' => $sortie,
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/checkout/{id}', name: 'checkout')]
    public function checkout(
        Sortie $sortie,
        Request $request,
        StripeClient $stripe,
        LoggerInterface $logger,
        EntityManagerInterface $em
    ): Response {
        // On récupère l'utilisateur connecté
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $logger->error('getUser() a retourné autre chose que App\Entity\User dans PaiementController::checkout()');
            $this->addFlash('danger', 'Vous devez être connecté avec un compte valide.');
            return $this->redirectToRoute('app_login');
        }

        // --- 1) Blocage si le user principal est déjà inscrit à cette sortie ---
        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // On crée une entité Paiement pour enregistrer la transaction localement
        $paiement = new Paiement();
        $paiement->setUtilisateur($user);
        $paiement->setSortie($sortie);

        // Récupération des IDs de membres sélectionnés depuis un formulaire (méthode POST)
        $idsMembres = $request->request->all('membres'); // ['me', '1', '3']

        $membresIds = array_filter($idsMembres, fn($id) => $id !== 'me');
        $membres = $em->getRepository(MembreFamille::class)->findBy(['id' => $membresIds]);

        // --- 2) Blocage si un membre de famille est déjà inscrit à cette sortie ---
        foreach ($membres as $membre) {
            if ($sortie->getMembresFamilleInscrits()->contains($membre)) {
                $this->addFlash(
                    'warning',
                    sprintf(
                        'Le membre « %s » est déjà inscrit à cette sortie.',
                        $membre->getPrenom() . ' ' . $membre->getNom()
                    )
                );
                return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
            }
        }

        $participants = count($membres);

        if (in_array('me', $idsMembres)) {
            $participants++;
        }

        // Vérifier la disponibilité des places
        $placesRestantes = $sortie->getNbInscriptionMax() - count($sortie->getParticipants()) - count($sortie->getMembresFamilleInscrits());
        if ($participants > $placesRestantes) {
            $this->addFlash('danger', sprintf('Il ne reste que %d place(s) disponible(s). Veuillez ajuster votre sélection.', $placesRestantes));
            return $this->redirectToRoute('paiement_choisir_membres', ['id' => $sortie->getId()]);
        }

        $paiement->setMontant($sortie->getPrix() * 100 * $participants);
        $paiement->setParticipants($participants);
        $em->persist($paiement);


        $successUrl = $this->generateUrl('paiement_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $this->generateUrl('paiement_cancel', ['sortie' => $sortie->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // dump($successUrl, $cancelUrl);

        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $sortie->getPrix() * 100,
                        'product_data' => [
                            'name' => $sortie->getTitre(),
                        ],
                    ],
                    'quantity' => $participants,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'idsMembres'     => implode(',', $membresIds),
                    'inscription_me' => in_array('me', $idsMembres) ? '1' : '0',
                ],
            ]);
        } catch (ApiErrorException $e) {
            $logger->error('Stripe API error during checkout: ' . $e->getMessage());
            $this->addFlash('danger', 'Une erreur est survenue lors de la création de votre paiement. Veuillez réessayer.');
            return $this->redirectToRoute('paiement_choisir_membres', ['id' => $sortie->getId()]);
        }

        $paiement->setStripeSessionId($session->id);

        $em->flush();

        // On redirige l'utilisateur vers la page de paiement Stripe
        return $this->redirect($session->url, 303);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/success', name: 'success')]
    public function success(
        Request $request,
        PaiementRepository $paiementRepository,
        MailerInterface $mailer,
        StripeClient $stripe,
        EntityManagerInterface $em,
        InscriptionService $inscriptionService
    ): Response
    {
        $sessionId = $request->query->get('session_id');
        $paiement = $paiementRepository->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$paiement) {
            throw $this->createNotFoundException('Aucun paiement trouvé pour cette session.');
        }

        // Récupère la session Stripe et crée les inscriptions si le webhook n'est pas encore passé
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        $metadata = $session->metadata;
        // Met à jour le statut et conserve les IDs pour remboursement
        $paiement->setStatut(Paiement::STATUT_PAYE);
        if (!empty($session->payment_intent)) {
            $paiement->setStripePaymentIntentId($session->payment_intent);
            // Récupère le PaymentIntent pour extraire l'ID de charge
            $pi = $stripe->paymentIntents->retrieve($session->payment_intent, ['expand' => ['charges']]);
            if (!empty($pi->charges->data[0]->id)) {
                $paiement->setStripeChargeId($pi->charges->data[0]->id);
            }
        }
        $paiement->setConfirmedAt(new \DateTimeImmutable());
        $em->persist($paiement);
        $em->flush();
        $sortie = $paiement->getSortie();
        $user = $paiement->getUtilisateur();

        // Finalise les inscriptions via le service
        $inscriptionService->finalizeInscription($paiement);

        $this->addFlash('success', 'Votre paiement a bien été confirmé.');

        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $email = (new Email())
                ->from('noreply-ecuriesdes4routes@gmail.com')
                ->to($user->getEmail())
                ->subject('Confirmation de votre inscription')
                ->text(sprintf(
                    "Bonjour %s,\n\nNous vous confirmons que votre paiement pour l'activité « %s » a bien été enregistré.\n\nMerci pour votre inscription et à bientôt aux Écuries des 4 Routes.",
                    $user->getPrenom(),
                    $paiement->getSortie()->getTitre()
                ));

            $mailer->send($email);
        }

        return $this->render('paiement/success.html.twig', [
            'paiement' => $paiement
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/cancel', name: 'cancel')]
    public function cancel(MailerInterface $mailer, EntityManagerInterface $em, Request $request): Response
    {
        // Cette méthode est appelée quand l'utilisateur annule le paiement ou ferme la page Stripe
        // Elle affiche un message d'annulation ou d'erreur
        $user = $this->getUser();
        $sortieId = $request->query->get('sortie');
        $sortie = $em->getRepository(Sortie::class)->find($sortieId);
        if ($user && $user instanceof \App\Entity\User) {
            $email = (new Email())
                // TODO : utiliser la vraie adresse email de l'écurie
                ->from('noreply@ecurie4routes.fr')
                ->to($user->getEmail())
                ->subject('Paiement non finalisé')
                ->text(sprintf(
                    "Bonjour %s,\n\nVous avez interrompu votre inscription à une activité. Aucun paiement n’a été effectué, et aucune inscription n’a été enregistrée.\n\nVous pouvez reprendre le processus à tout moment depuis votre espace membre.\n\nMerci,\nLes Écuries des 4 Routes",
                    $user->getPrenom()
                ));

            $mailer->send($email);

            $this->addFlash('warning', 'Votre paiement a été annulé.');

            if ($user && $sortie) {
                $log = new HistoriquePaiement();
                $log->setType('paiement_annule');
                $log->setDate(new \DateTimeImmutable());
                $log->setMessage(sprintf("Paiement annulé pour la sortie « %s ».", $sortie->getTitre()));
                $log->setUtilisateur($user);
                $log->setSortie($sortie);
                $em->persist($log);
                $em->flush();
            }
        }
        return $this->render('paiement/cancel.html.twig');
    }

    #[Route('/webhook/stripe', name: 'webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        PaiementRepository $repo,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        StripeClient $stripe,
        InscriptionService $inscriptionService
    ): Response {
        // Stripe envoie une requête POST ici pour notifier un paiement terminé
        // On récupère le contenu brut de la requête (JSON)
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');
        $secret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        // On vérifie la signature de Stripe pour s'assurer que la requête est authentique
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }

        $logger->info('Stripe webhook received: ' . $event->type);

        // Si l'événement Stripe correspond à une session de paiement terminée
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            // Retrieve full session with payment_intent and charges expanded
            $session = $stripe->checkout->sessions->retrieve(
                $session->id,
                ['expand' => ['payment_intent', 'payment_intent.charges']]
            );
            $logger->info('Processing checkout.session.completed for session ' . ($session->id ?? '[no id]'));

            if (!isset($session->id)) {
                return new Response('Session ID manquant', 400);
            }

            $paiement = $repo->findOneBy(['stripeSessionId' => $session->id]);

            // On met à jour le statut du paiement dans la base de données
            if ($paiement) {
                $paiement->setStatut(Paiement::STATUT_PAYE);
                $paiement->setConfirmedAt(new \DateTimeImmutable());

                // Store PaymentIntent and Charge ID for future refunds
                if (!empty($session->payment_intent)) {
                    $paiement->setStripePaymentIntentId($session->payment_intent->id);
                    if (!empty($session->payment_intent->charges->data[0]->id)) {
                        $paiement->setStripeChargeId($session->payment_intent->charges->data[0]->id);
                    }
                }

                $em->flush();

                // Finalise les inscriptions via le service
                $inscriptionService->finalizeInscription($paiement);
            }
        }

        return new Response('OK', 200);
    }
}