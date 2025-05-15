<?php

namespace App\Controller;

use App\Entity\Cheval;
use App\Entity\Niveau;
use App\Entity\User;
use App\Form\ChevalType;
use App\Form\NiveauType;
use App\Repository\NiveauRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{

    #[Route('/', name: 'app_admin_index', methods: ['GET'])]
    public function index(){
        return $this->render('admin/index.html.twig', []);
    }



    //CUD Cheval



    #[Route('/cheval/ajouter', name: 'app_cheval_new', methods: ['GET', 'POST'])]
    public function newCheval(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cheval = new Cheval();
        $form = $this->createForm(ChevalType::class, $cheval);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cheval);
            $entityManager->flush();

            return $this->redirectToRoute('app_cheval_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cheval/new.html.twig', [
            'cheval' => $cheval,
            'form' => $form,
        ]);
    }



    #[Route('/cheval/{id}', name: 'app_cheval_delete', methods: ['POST'])]
    public function deleteCheval(Request $request, Cheval $cheval, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cheval->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cheval);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cheval_index', [], Response::HTTP_SEE_OTHER);
    }



    //CRUD utilisateur


}