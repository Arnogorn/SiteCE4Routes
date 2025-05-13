<?php

namespace App\Controller;

use App\Entity\MembreFamille;
use App\Form\MembreFamilleType;
use App\Repository\MembreFamilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/membre/famille')]
final class MembreFamilleController extends AbstractController
{
    #[Route(name: 'app_membre_famille_index', methods: ['GET'])]
    public function index(MembreFamilleRepository $membreFamilleRepository): Response
    {
        $famille = $this->getUser()->getFamille();
        return $this->render('membre_famille/index.html.twig', [
            'membre_familles' => $membreFamilleRepository->findBy(['famille' => $famille]),
        ]);
    }

    #[Route('/new', name: 'app_membre_famille_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $membreFamille = new MembreFamille();
        $form = $this->createForm(MembreFamilleType::class, $membreFamille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $membreFamille->setFamille($this->getUser()->getFamille());
            $entityManager->persist($membreFamille);
            $entityManager->flush();

            return $this->redirectToRoute('app_membre_famille_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membre_famille/new.html.twig', [
            'membre_famille' => $membreFamille,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membre_famille_show', methods: ['GET'])]
    public function show(MembreFamille $membreFamille): Response
    {
        if ($membreFamille->getFamille() !== $this->getUser()->getFamille()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('membre_famille/show.html.twig', [
            'membre_famille' => $membreFamille,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_membre_famille_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MembreFamille $membreFamille, EntityManagerInterface $entityManager): Response
    {
        if ($membreFamille->getFamille() !== $this->getUser()->getFamille()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MembreFamilleType::class, $membreFamille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_membre_famille_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membre_famille/edit.html.twig', [
            'membre_famille' => $membreFamille,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membre_famille_delete', methods: ['POST'])]
    public function delete(Request $request, MembreFamille $membreFamille, EntityManagerInterface $entityManager): Response
    {
        if ($membreFamille->getFamille() !== $this->getUser()->getFamille()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$membreFamille->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($membreFamille);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_membre_famille_index', [], Response::HTTP_SEE_OTHER);
    }
}
