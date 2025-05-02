<?php

namespace App\Controller;

use App\Entity\Cheval;
use App\Form\ChevalType;
use App\Repository\ChevalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cheval')]
final class ChevalController extends AbstractController
{
    #[Route(name: 'app_cheval_index', methods: ['GET'])]
    public function index(ChevalRepository $chevalRepository): Response
    {
        return $this->render('cheval/index.html.twig', [
            'chevals' => $chevalRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'app_cheval_show', methods: ['GET'])]
    public function show(Cheval $cheval): Response
    {
        return $this->render('cheval/show.html.twig', [
            'cheval' => $cheval,
        ]);
    }




}
