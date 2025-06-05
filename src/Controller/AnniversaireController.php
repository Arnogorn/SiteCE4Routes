<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AnniversaireController extends AbstractController
{
#[Route('/anniversaire', name: 'anniversaire')]
public function anniversaire(){
    return $this->render('anniversaire/index.html.twig');
}
}