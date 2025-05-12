<?php

namespace App\Controller;

use App\Repository\ChevalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CommerceController extends AbstractController
{
    #[Route('/commerce', name: 'commerce', methods:['GET'])]
public function index(ChevalRepository $chevalRepository){

        $chevalAVendre = $chevalRepository->findBy(['aVendre' => true]);
        $chevalVendu = $chevalRepository->findBy(['vendu' => true]);
        return $this->render('commerce/index.html.twig',[
            'chevauxAVendre' => $chevalAVendre,
            'chevauxVendu' => $chevalVendu
                ]
        );
    }

}