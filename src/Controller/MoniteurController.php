<?php

namespace App\Controller;

use App\Entity\Moniteur;
use App\Form\MoniteurForm;
use App\Repository\MoniteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/moniteur')]
final class MoniteurController extends AbstractController
{
    #[Route(name: 'app_moniteur_index', methods: ['GET'])]
    public function index(MoniteurRepository $moniteurRepository): Response
    {
        return $this->render('moniteur/index.html.twig', [
            'moniteurs' => $moniteurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_moniteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moniteur = new Moniteur();
        $form = $this->createForm(MoniteurForm::class, $moniteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($moniteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_moniteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('moniteur/new.html.twig', [
            'moniteur' => $moniteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_moniteur_show', methods: ['GET'])]
    public function show(Moniteur $moniteur): Response
    {
        return $this->render('moniteur/show.html.twig', [
            'moniteur' => $moniteur,
        ]);
    }



    #[Route('/{id}', name: 'app_moniteur_delete', methods: ['POST'])]
    public function delete(Request $request, Moniteur $moniteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$moniteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($moniteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_moniteur_index', [], Response::HTTP_SEE_OTHER);
    }
}
