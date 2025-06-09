<?php

namespace App\Controller;

use App\Entity\Cheval;
use App\Entity\Niveau;
use App\Entity\User;
use App\Form\ChevalType;
use App\Form\NiveauType;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\NiveauRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{

    #[Route('/', name: 'app_admin_index', methods: ['GET'])]
    public function index(){
        return $this->render('admin/index.html.twig', []);
    }

    // CRUD Niveau
    #[Route('/niveau',name: 'app_niveau_index', methods: ['GET'])]
    public function indexNiveau(NiveauRepository $niveauRepository): Response
    {
        return $this->render('niveau/index.html.twig', [
            'niveaux' => $niveauRepository->findAll(),
        ]);
    }

    #[Route('/niveau/ajouter', name: 'app_niveau_new', methods: ['GET', 'POST'])]
    public function newNiveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $niveau = new Niveau();
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($niveau);
            $entityManager->flush();

            return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('niveau/new.html.twig', [
            'niveau' => $niveau,
            'form' => $form,
        ]);
    }

    #[Route('/niveau/{id}', name: 'app_niveau_show', methods: ['GET'])]
    public function showNiveau(Niveau $niveau): Response
    {
        return $this->render('niveau/show.html.twig', [
            'niveau' => $niveau,
        ]);
    }

    #[Route('/niveau/{id}/modifier', name: 'app_niveau_edit', methods: ['GET', 'POST'])]
    public function editNiveau(Request $request, Niveau $niveau, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('niveau/edit.html.twig', [
            'niveau' => $niveau,
            'form' => $form,
        ]);
    }

    #[Route('/niveau/{id}', name: 'app_niveau_delete', methods: ['POST'])]
    public function deleteNiveau(Request $request, Niveau $niveau, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$niveau->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($niveau);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
    }




    //CUD Cheval



    #[Route('/cheval/ajouter', name: 'app_cheval_new', methods: ['GET', 'POST'])]
    public function newCheval(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cheval = new Cheval();
        $form = $this->createForm(ChevalType::class, $cheval);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cheval);
            $entityManager->flush();

            return $this->redirectToRoute('app_cheval_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cheval/new.html.twig', [
            'cheval' => $cheval,
            'form' => $form,
        ]);
    }



    #[Route('/cheval/{id}', name: 'app_cheval_delete', methods: ['POST'])]
    public function deleteCheval(Request $request, Cheval $cheval, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cheval->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cheval);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cheval_index', [], Response::HTTP_SEE_OTHER);
    }



    //CRUD utilisateur

    #[Route('/utilisateur/{id}/diagnose', name: 'admin_user_diagnose', methods: ['GET'])]
    public function diagnoseUser(
        User $user,
        UserRepository $userRepository
    ): Response {
        try {
            $problems = $userRepository->diagnoseProblem($user);

            $this->addFlash('info', 'Diagnostic pour ' . $user->getPrenom() . ' ' . $user->getNom());

            foreach ($problems as $key => $value) {
                $this->addFlash('secondary', sprintf('%s: %s', $key, $value));
            }

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur diagnostic: ' . $e->getMessage());
        }

        return $this->redirectToRoute('index');
    }

    #[Route('/utilisateur/{id}/analyze-payments', name: 'admin_user_analyze_payments', methods: ['GET'])]
    public function analyzeUserPayments(
        User $user,
        UserRepository $userRepository
    ): Response {
        try {
            $analysis = $userRepository->analyzeUserPayments($user);

            // Messages d'information
            $this->addFlash('info', sprintf(
                'Analyse pour %s %s - %d paiement(s) total',
                $user->getPrenom(),
                $user->getNom(),
                $analysis['total_paiements']
            ));

            // Informations sur les inscriptions
            if ($analysis['inscriptions_liees'] > 0) {
                $this->addFlash('info', sprintf(
                    '📝 %d inscription(s) liée(s) aux paiements',
                    $analysis['inscriptions_liees']
                ));
            }

            if ($analysis['inscriptions_membres_famille'] > 0) {
                $this->addFlash('info', sprintf(
                    '👥 %d inscription(s) de membres de famille',
                    $analysis['inscriptions_membres_famille']
                ));
            }

            if ($analysis['paiements_payes'] > 0) {
                $this->addFlash('warning', sprintf(
                    '💳 %d paiement(s) confirmé(s) pour %.2f€ - Remboursements à traiter manuellement',
                    $analysis['paiements_payes'],
                    $analysis['montant_total_paye']
                ));

                // Détail des paiements confirmés avec participants
                foreach ($analysis['details_paiements_payes'] as $detail) {
                    $this->addFlash('info', sprintf(
                        '• %.2f€ - %s (%s) - %d participant(s) - Session: %s',
                        $detail['montant'],
                        $detail['sortie'],
                        $detail['date'],
                        $detail['participants'],
                        $detail['stripe_session']
                    ));
                }
            }

            if ($analysis['paiements_en_attente'] > 0) {
                $this->addFlash('secondary', sprintf(
                    '⏳ %d paiement(s) en attente (seront annulés)',
                    $analysis['paiements_en_attente']
                ));
            }

            if ($analysis['nb_membres_famille'] > 0) {
                $this->addFlash('info', sprintf(
                    '👥 %d membre(s) de famille seront supprimés',
                    $analysis['nb_membres_famille']
                ));
            }

            if ($analysis['total_paiements'] === 0) {
                $this->addFlash('success', '✅ Aucun paiement - Suppression directe possible');
            }

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur analyse: ' . $e->getMessage());
        }

        return $this->redirectToRoute('index');
    }


    #[Route('/utilisateur/{id}/delete-complete', name: 'admin_user_delete_complete', methods: ['POST'])]
    public function deleteUserComplete(
        User $user,
        UserRepository $userRepository,
        Request $request
    ): Response {
        // Vérification CSRF
        $tokenName = 'delete_user_' . $user->getId();
        $tokenFromRequest = $request->request->get('_token');

        if (!$this->isCsrfTokenValid($tokenName, $tokenFromRequest)) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('index');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous supprimer vous-même.');
            return $this->redirectToRoute('index');
        }

        try {
            // Analyse rapide pour info
            $analysis = $userRepository->analyzeUserPayments($user);

            // Suppression directe (gère automatiquement les paiements)
            $userRepository->deleteUserWithAllRelations($user);

            // Messages de succès
            $this->addFlash('success', sprintf(
                '✅ Utilisateur %s %s supprimé avec succès',
                $user->getPrenom(),
                $user->getNom()
            ));

            if ($analysis['inscriptions_liees'] > 0) {
                $this->addFlash('info', sprintf(
                    'Supprimé: %d inscription(s) liée(s) aux paiements',
                    $analysis['inscriptions_liees']
                ));
            }

            if ($analysis['total_paiements'] > 0) {
                $this->addFlash('info', sprintf(
                    'Supprimé: %d paiement(s) dont %d confirmé(s)',
                    $analysis['total_paiements'],
                    $analysis['paiements_payes']
                ));

                if ($analysis['paiements_payes'] > 0) {
                    $this->addFlash('warning', sprintf(
                        '💰 %.2f€ de paiements confirmés supprimés - Penser aux remboursements Stripe',
                        $analysis['montant_total_paye']
                    ));
                }
            }

            if ($analysis['nb_membres_famille'] > 0) {
                $this->addFlash('info', sprintf(
                    'Famille supprimée: %d membre(s)',
                    $analysis['nb_membres_famille']
                ));
            }

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('index');
    }
}