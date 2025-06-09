<?php

namespace App\Repository;

use App\Entity\Inscription;
use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function deleteUserWithAllRelations(User $user): void
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            // ÉTAPE 1: Supprimer toutes les inscriptions liées aux paiements de cet utilisateur
            $this->removeInscriptionsForUser($user, $em);

            // ÉTAPE 2: Supprimer les inscriptions des membres de famille
            $this->removeInscriptionsForFamilyMembers($user, $em);

            // ÉTAPE 3: Supprimer les paiements de l'utilisateur
            $this->removePaiements($user, $em);

            // ÉTAPE 4: Gérer la famille et les membres
            $this->handleFamilyAndMembers($user, $em);

            // ÉTAPE 5: Retirer l'utilisateur des sorties
            $this->removeUserFromSorties($user, $em);

            // ÉTAPE 6: Supprimer l'historique des paiements
            $this->removePaymentHistory($user, $em);

            // ÉTAPE 7: Supprimer l'utilisateur
            $em->remove($user);

            $em->flush();
            $em->commit();

        } catch (\Exception $e) {
            $em->rollback();
            throw new \Exception('Erreur lors de la suppression complète : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Supprime toutes les inscriptions liées aux paiements de l'utilisateur
     */
    private function removeInscriptionsForUser(User $user, $em): void
    {
        $inscriptionRepo = $em->getRepository(Inscription::class);

        // Inscriptions où l'utilisateur est le participant
        $inscriptionsUtilisateur = $inscriptionRepo->createQueryBuilder('i')
            ->join('i.paiement', 'p')
            ->where('p.utilisateur = :user')
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        foreach ($inscriptionsUtilisateur as $inscription) {
            error_log(sprintf(
                "SUPPRESSION INSCRIPTION USER - %s pour sortie %s (paiement %.2f€)",
                $user->getEmail(),
                $inscription->getSortie()->getTitre(),
                $inscription->getPaiement()->getMontantEuros()
            ));
            $em->remove($inscription);
        }

        // Inscriptions où l'utilisateur a payé pour quelqu'un d'autre
        $inscriptionsPayeesParUser = $inscriptionRepo->createQueryBuilder('i')
            ->join('i.paiement', 'p')
            ->where('p.utilisateur = :user')
            ->andWhere('i.utilisateur != :user OR i.utilisateur IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        foreach ($inscriptionsPayeesParUser as $inscription) {
            $participant = $inscription->getUtilisateur()
                ? $inscription->getUtilisateur()->getEmail()
                : ($inscription->getMembreFamille()
                    ? $inscription->getMembreFamille()->getPrenom() . ' ' . $inscription->getMembreFamille()->getNom()
                    : 'Inconnu');

            error_log(sprintf(
                "SUPPRESSION INSCRIPTION PAYÉE PAR USER - %s a payé pour %s, sortie %s (%.2f€)",
                $user->getEmail(),
                $participant,
                $inscription->getSortie()->getTitre(),
                $inscription->getPaiement()->getMontantEuros()
            ));
            $em->remove($inscription);
        }
    }

    /**
     * Supprime les inscriptions des membres de famille
     */
    private function removeInscriptionsForFamilyMembers(User $user, $em): void
    {
        if (!$user->getFamille()) {
            return;
        }

        $inscriptionRepo = $em->getRepository(Inscription::class);

        foreach ($user->getFamille()->getMembre() as $membre) {
            // Inscriptions du membre de famille
            $inscriptionsMembre = $inscriptionRepo->createQueryBuilder('i')
                ->where('i.membreFamille = :membre')
                ->setParameter('membre', $membre)
                ->getQuery()
                ->getResult();

            foreach ($inscriptionsMembre as $inscription) {
                error_log(sprintf(
                    "SUPPRESSION INSCRIPTION MEMBRE - %s %s pour sortie %s",
                    $membre->getPrenom(),
                    $membre->getNom(),
                    $inscription->getSortie()->getTitre()
                ));
                $em->remove($inscription);
            }
        }
    }

    /**
     * Supprime tous les paiements de l'utilisateur
     */
    private function removePaiements(User $user, $em): void
    {
        $paiementRepo = $em->getRepository(Paiement::class);
        $paiements = $paiementRepo->findBy(['utilisateur' => $user]);

        foreach ($paiements as $paiement) {
            // Log simple pour traçabilité si paiement confirmé
            if ($paiement->getStatut() === Paiement::STATUT_PAYE) {
                error_log(sprintf(
                    "SUPPRESSION PAIEMENT CONFIRMÉ - User: %s, Montant: %.2f€, Sortie: %s, Stripe Session: %s, Payment Intent: %s",
                    $user->getEmail(),
                    $paiement->getMontantEuros(),
                    $paiement->getSortie()->getTitre(),
                    $paiement->getStripeSessionId(),
                    $paiement->getStripePaymentIntentId() ?? 'N/A'
                ));
            }

            $em->remove($paiement);
        }
    }

    /**
     * Gère la famille et les membres
     */
    private function handleFamilyAndMembers(User $user, $em): void
    {
        $famille = $user->getFamille();
        if (!$famille) {
            return;
        }

        // Copie pour éviter les modifications pendant l'itération
        $membres = $famille->getMembre()->toArray();

        foreach ($membres as $membre) {
            // Retirer le membre des sorties
            $sorties = $membre->getSorties();
            foreach ($sorties as $sortie) {
                $sortie->removeMembresFamilleInscrit($membre);
            }

            $em->remove($membre);
        }

        // Supprimer la famille
        $em->remove($famille);
    }

    /**
     * Retire l'utilisateur de ses sorties
     */
    private function removeUserFromSorties(User $user, $em): void
    {
        $sorties = $user->getSorties();
        foreach ($sorties as $sortie) {
            $sortie->removeParticipant($user);
        }
    }

    /**
     * Supprime l'historique des paiements
     */
    private function removePaymentHistory(User $user, $em): void
    {
        $historiqueRepo = $em->getRepository('App\Entity\HistoriquePaiement');
        $historiques = $historiqueRepo->findBy(['utilisateur' => $user]);

        foreach ($historiques as $historique) {
            $em->remove($historique);
        }
    }

    /**
     * Analyse complète incluant les inscriptions
     */
    public function analyzeUserPayments(User $user): array
    {
        $em = $this->getEntityManager();
        $paiementRepo = $em->getRepository(Paiement::class);
        $inscriptionRepo = $em->getRepository(Inscription::class);

        $paiements = $paiementRepo->findBy(['utilisateur' => $user]);

        $analysis = [
            'total_paiements' => count($paiements),
            'paiements_payes' => 0,
            'paiements_en_attente' => 0,
            'montant_total_paye' => 0,
            'details_paiements_payes' => [],
            'inscriptions_liees' => 0
        ];

        // Compter les inscriptions liées aux paiements de cet utilisateur
        $inscriptionsLiees = $inscriptionRepo->createQueryBuilder('i')
            ->join('i.paiement', 'p')
            ->where('p.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $analysis['inscriptions_liees'] = count($inscriptionsLiees);

        // Analyser les paiements
        foreach ($paiements as $paiement) {
            if ($paiement->getStatut() === Paiement::STATUT_PAYE) {
                $analysis['paiements_payes']++;
                $analysis['montant_total_paye'] += $paiement->getMontantEuros();

                $analysis['details_paiements_payes'][] = [
                    'montant' => $paiement->getMontantEuros(),
                    'sortie' => $paiement->getSortie()->getTitre(),
                    'stripe_session' => $paiement->getStripeSessionId(),
                    'stripe_payment_intent' => $paiement->getStripePaymentIntentId(),
                    'date' => $paiement->getCreatedAt()->format('d/m/Y'),
                    'participants' => $paiement->getParticipants()
                ];
            } elseif ($paiement->getStatut() === Paiement::STATUT_EN_ATTENTE) {
                $analysis['paiements_en_attente']++;
            }
        }

        // Info famille
        $analysis['nb_membres_famille'] = $user->getFamille() ? count($user->getFamille()->getMembre()) : 0;

        // Inscriptions des membres de famille
        if ($user->getFamille()) {
            $inscriptionsMembres = $inscriptionRepo->createQueryBuilder('i')
                ->join('i.membreFamille', 'm')
                ->where('m.famille = :famille')
                ->setParameter('famille', $user->getFamille())
                ->getQuery()
                ->getResult();

            $analysis['inscriptions_membres_famille'] = count($inscriptionsMembres);
        } else {
            $analysis['inscriptions_membres_famille'] = 0;
        }

        return $analysis;
    }

    /**
     * Diagnostic détaillé pour comprendre les contraintes
     */
    public function diagnoseProblem(User $user): array
    {
        $em = $this->getEntityManager();
        $inscriptionRepo = $em->getRepository(Inscription::class);
        $paiementRepo = $em->getRepository(Paiement::class);

        $problems = [];

        // Paiements de l'utilisateur
        $paiements = $paiementRepo->findBy(['utilisateur' => $user]);
        $problems['paiements_utilisateur'] = count($paiements);

        // Inscriptions liées aux paiements de l'utilisateur
        $inscriptionsUser = $inscriptionRepo->createQueryBuilder('i')
            ->join('i.paiement', 'p')
            ->where('p.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        $problems['inscriptions_avec_paiements_user'] = count($inscriptionsUser);

        // Inscriptions des membres de famille
        if ($user->getFamille()) {
            $problems['membres_famille'] = count($user->getFamille()->getMembre());

            $inscriptionsMembres = $inscriptionRepo->createQueryBuilder('i')
                ->join('i.membreFamille', 'm')
                ->where('m.famille = :famille')
                ->setParameter('famille', $user->getFamille())
                ->getQuery()
                ->getResult();
            $problems['inscriptions_membres_famille'] = count($inscriptionsMembres);
        } else {
            $problems['membres_famille'] = 0;
            $problems['inscriptions_membres_famille'] = 0;
        }

        // Inscriptions où l'utilisateur a inscrit quelqu'un
        $inscriptionsInscritePar = $inscriptionRepo->findBy(['inscritPar' => $user]);
        $problems['inscriptions_inscrites_par_user'] = count($inscriptionsInscritePar);

        return $problems;
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
