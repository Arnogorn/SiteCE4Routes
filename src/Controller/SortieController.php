<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Services\UpdateEtatService;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository, Security $security,UpdateEtatService $updateEtatService,): Response
    {
        $user = $security->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        if ($isAdmin) {
            // Les admins voient toutes les sorties
            $sorties = $sortieRepository->findAll();
        } else {
            // Les autres utilisateurs ne voient pas les sorties à l'état "Créée"
            $sorties = $sortieRepository->findByEtatDifferentDe('Créée');
        }
        $updateEtatService->updateEtat($sorties);

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ajouter', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($sortie->getDate() !== null) {
                $dateLimite = $sortie->getDate()->sub(new DateInterval('P2D'));
                $sortie->setDateLimiteInscription($dateLimite);
            }
            if ($sortie->isPublished() === true) {
                // Récupérer l'état "Ouvert" depuis la base de données
                $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouverte']);


                if ($etatOuvert) {
                    $sortie->setEtat($etatOuvert);
                } else {
                    // Gestion d'erreur si l'état n'existe pas
                    $this->addFlash('danger', 'Impossible de trouver l\'état "Ouvert". Veuillez vérifier votre configuration.');

                }
            } else {
                // Si non publiée, définir un état par défaut, par exemple "Créée"
                $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
                if ($etatCree) {
                    $sortie->setEtat($etatCree);
                }
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {


        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription')]
    public function inscription(Sortie $sortie, EntityManagerInterface $em, Security $security): Response
    {

        //TODO gérer les conditions d'inscription aux sorties (en fonction de l'état)
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        if ($sortie->getDate() < new \DateTime()) {
            $this->addFlash('danger', 'Cette sortie est déjà terminée, vous ne pouvez plus vous inscrire.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifie si déjà inscrit
        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
        } elseif ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('danger', 'La date limite d\'inscription est dépassée.');
        } elseif ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()) {
            $this->addFlash('danger', 'Cette sortie est complète.');
        } else {
            $sortie->addParticipant($user);
            $em->flush();
            $this->addFlash('success', 'Inscription réussie !');
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desinscription', name: 'sortie_desinscription')]
    public function desinscription(Sortie $sortie, EntityManagerInterface $em, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous désinscrire.');
        }

        if (!$sortie->getParticipants()->contains($user)) {
            $this->addFlash('info', 'Vous n\'êtes pas inscrit à cette sortie.');
        } else {
            $sortie->removeParticipant($user);            $em->flush();
            $this->addFlash('success', 'Vous vous êtes désinscrit de la sortie.');
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/sortie/publier/{id}', name:'sortie_publier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function publierSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
    ){



        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);


        if (!$etatOuverte) {
            $this->addFlash('error', 'État "Ouverte" introuvable.');
            return $this->redirectToRoute('sortie');
        }

        // Changer l'état de la sortie en Ouverte
        $sortie->setEtat($etatOuverte);

        // Save changes
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'La sortie a été publiée avec succès !');

        // Redirect to sortie details page
        return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
    }

    /// Inscription de MembreFamille ///
    #[Route('/{id}/inscription-famille', name: 'sortie_inscription_famille', methods: ['GET', 'POST'])]
    public function inscriptionFamille(Sortie $sortie, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user || !$user->getFamille()) {
            throw $this->createAccessDeniedException('Aucune famille trouvée.');
        }

        $membres = $user->getFamille()->getMembre();

        if ($request->isMethod('POST')) {
            $ids = $request->request->all('membres'); // tableau d’IDs cochés
            foreach ($membres as $membre) {
                if (in_array($membre->getId(), $ids)) {
                    $sortie->addMembresFamilleInscrit($membre);
                    $membre->addSortie($sortie);
                } else {
                    $sortie->removeMembresFamilleInscrit($membre);
                    $membre->removeSortie($sortie);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Membres inscrits avec succès.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/inscription_famille.html.twig', [
            'sortie' => $sortie,
            'membres' => $membres,
            'membresDejaInscrits' => $sortie->getMembresFamilleInscrits(),
        ]);
    }

}
