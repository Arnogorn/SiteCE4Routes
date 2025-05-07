<?php

namespace App\Controller;

use App\Entity\EcurieProprietaire;
use App\Form\EcurieProprietaireType;
use App\Repository\EcurieProprietaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ecurie-proprietaire')]
final class EcurieProprietaireController extends AbstractController
{
    #[Route(name: 'app_ecurie_proprietaire_index', methods: ['GET'])]
    public function index(EcurieProprietaireRepository $ecurieProprietaireRepository): Response
    {
        return $this->render('ecurie_proprietaire/index.html.twig', [
            'ecurie_proprietaires' => $ecurieProprietaireRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/nouveau', name: 'app_ecurie_proprietaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ecurieProprietaire = new EcurieProprietaire();
        $form = $this->createForm(EcurieProprietaireType::class, $ecurieProprietaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ecurieProprietaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_ecurie_proprietaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ecurie_proprietaire/new.html.twig', [
            'ecurie_proprietaire' => $ecurieProprietaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ecurie_proprietaire_show', methods: ['GET'])]
    public function show(EcurieProprietaire $ecurieProprietaire): Response
    {
        return $this->render('ecurie_proprietaire/show.html.twig', [
            'ecurie_proprietaire' => $ecurieProprietaire,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/modifier', name: 'app_ecurie_proprietaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EcurieProprietaire $ecurieProprietaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EcurieProprietaireType::class, $ecurieProprietaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ecurie_proprietaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ecurie_proprietaire/edit.html.twig', [
            'ecurie_proprietaire' => $ecurieProprietaire,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_ecurie_proprietaire_delete', methods: ['POST'])]
    public function delete(Request $request, EcurieProprietaire $ecurieProprietaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ecurieProprietaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ecurieProprietaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ecurie_proprietaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
