<?php

namespace App\Controller;

use App\Entity\Calendrier;
use App\Form\CalendrierType;
use App\Repository\CalendrierRepository;
use App\Service\GrilleCalendrierService;
use App\Service\GestionCalendrierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/calendrier')]
class CalendrierController extends AbstractController
{
    #[Route('/', name: 'app_calendrier_index', methods: ['GET'])]
    public function index(
        CalendrierRepository $calendrierRepository,
        GrilleCalendrierService $grilleService
    ): Response {
        return $this->render('calendrier/index.html.twig', [
            'jours' => $grilleService->getJours(),
            'heures' => $grilleService->getHeures(),
            'grille' => $grilleService->creerGrille($calendrierRepository),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/nouveau', name: 'app_calendrier_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        GestionCalendrierService $gestionService
    ): Response {
        $calendrier = $gestionService->initialiserNouveauCalendrier(
            $request->query->get('jour'),
            $request->query->get('heure')
        );

        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resultat = $gestionService->validerEtSauvegarder($calendrier);

            $this->addFlash($resultat['success'] ? 'success' : 'error', $resultat['message']);

            if ($resultat['success']) {
                return $this->redirectToRoute('app_calendrier_index');
            }
        }

        return $this->render('calendrier/new.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/modifier', name: 'app_calendrier_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Calendrier $calendrier,
        GestionCalendrierService $gestionService
    ): Response {
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resultat = $gestionService->validerEtSauvegarder($calendrier);

            $this->addFlash($resultat['success'] ? 'success' : 'error', $resultat['message']);

            if ($resultat['success']) {
                return $this->redirectToRoute('app_calendrier_index');
            }
        }

        return $this->render('calendrier/edit.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_calendrier_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Calendrier $calendrier,
        GestionCalendrierService $gestionService
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$calendrier->getId(), $request->request->get('_token'))) {
            $gestionService->supprimer($calendrier);
            $this->addFlash('success', 'Événement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_calendrier_index');
    }
}