<?php

namespace App\Controller;

use App\Entity\Tarifs;
use App\Form\TarifsForm;
use App\Repository\TarifsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tarifs')]
final class TarifsController extends AbstractController
{
    #[Route(name: 'app_tarifs_index', methods: ['GET'])]
    public function index(TarifsRepository $tarifsRepository): Response
    {
        return $this->render('tarifs/index.html.twig', [
            'tarifs' => $tarifsRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_tarifs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tarif = new Tarifs();
        $form = $this->createForm(TarifsForm::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tarif);
            $entityManager->flush();

            return $this->redirectToRoute('app_tarifs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tarifs/new.html.twig', [
            'tarif' => $tarif,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_tarifs_show', methods: ['GET'])]
    public function show(Tarifs $tarif): Response
    {
        return $this->render('tarifs/show.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_tarifs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tarifs $tarif, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TarifsForm::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tarifs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tarifs/edit.html.twig', [
            'tarif' => $tarif,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_tarifs_delete', methods: ['POST'])]
    public function delete(Request $request, Tarifs $tarif, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tarif->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tarif);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tarifs_index', [], Response::HTTP_SEE_OTHER);
    }
}
