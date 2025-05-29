<?php

namespace App\Controller;

use App\Entity\TypeSortie;
use App\Form\TypeSortieForm;
use App\Repository\TypeSortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/categorie-sortie')]
final class TypeSortieController extends AbstractController
{
    #[Route(name: 'app_type_sortie_index', methods: ['GET'])]
    public function index(TypeSortieRepository $typeSortieRepository): Response
    {
        return $this->render('type_sortie/index.html.twig', [
            'type_sorties' => $typeSortieRepository->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_type_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeSortie = new TypeSortie();
        $form = $this->createForm(TypeSortieForm::class, $typeSortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeSortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_sortie/new.html.twig', [
            'type_sortie' => $typeSortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_sortie_show', methods: ['GET'])]
    public function show(TypeSortie $typeSortie): Response
    {
        return $this->render('type_sortie/show.html.twig', [
            'type_sortie' => $typeSortie,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_type_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeSortie $typeSortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeSortieForm::class, $typeSortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_sortie/edit.html.twig', [
            'type_sortie' => $typeSortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, TypeSortie $typeSortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeSortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeSortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_sortie_index', [], Response::HTTP_SEE_OTHER);
    }
}
