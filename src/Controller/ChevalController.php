<?php

namespace App\Controller;

use App\Entity\Cheval;
use App\Form\ChevalSearchType;
use App\Form\ChevalType;
use App\Repository\ChevalRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/cheval')]
final class ChevalController extends AbstractController
{
    #[Route(name: 'app_cheval_index', methods: ['GET'])]
    public function index(Request $request, ChevalRepository $chevalRepository): Response
    {
        // Récupération du terme de recherche
        $search = $request->query->get('search');

        // Formulaire de recherche
        $searchForm = $this->createForm(ChevalSearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false
        ]);
        $searchForm->handleRequest($request);

        // Récupération des données avec une seule requête optimisée
        $chevaux = $chevalRepository->findAllWithRelations($search);

        // Récupération des statistiques en une seule requête
        $statistics = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            $statistics = $chevalRepository->getStatistics();
        }

        return $this->render('cheval/index.html.twig', [
            'chevaux' => $chevaux,
            'search_form' => $searchForm->createView(),
            'statistics' => $statistics,
            'current_search' => $search
        ]);
    }



    #[Route('/{id}', name: 'app_cheval_show', methods: ['GET'])]
    public function show(Cheval $cheval): Response
    {
        $hasPhoto = false;
        if ($cheval->getPhoto() !== null && file_exists($this->getParameter('cheval_picture_dir') . '/' . $cheval->getPhoto())) {
            $hasPhoto = true;
        }

        return $this->render('cheval/show.html.twig', [
            'cheval' => $cheval,
            'hasPhoto' => $hasPhoto
        ]);
    }


    #[Route('/cheval/{id}/modifier', name: 'app_cheval_edit', methods: ['GET', 'POST'])]
    public function editCheval(Request $request, Cheval $cheval, EntityManagerInterface $entityManager, Security $security, Uploader $uploader): Response
    {
        // Vérifier les permissions
        $currentUser = $security->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $owner = $cheval->getUser(); // Assurez-vous que cette méthode corresponde à votre structure

        if (!$isAdmin && (!$owner || $owner->getId() !== $currentUser->getId())) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier ce cheval.');
            return $this->redirectToRoute('app_cheval_show', ['id' => $cheval->getId()]);
        }

        // Créer le formulaire avec le flag isAdmin
        $form = $this->createForm(ChevalType::class, $cheval, [
            'is_admin' => $isAdmin
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('photo')->getData();
            if ($picture) {
                // Supprimer la photo précédente si elle existait
                if ($cheval->getPhoto()) {
                    $uploader->delete($cheval->getPhoto(), $this->getParameter('cheval_picture_dir'));
                }
                $fileName = $uploader->save($picture, $cheval->getNom(), $this->getParameter('cheval_picture_dir'));
                $cheval->setPhoto($fileName);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Les informations du cheval ont été mises à jour.');
            return $this->redirectToRoute('app_cheval_show', ['id' => $cheval->getId()]);
        }

        return $this->render('cheval/edit.html.twig', [
            'cheval' => $cheval,
            'form' => $form->createView(),
            'is_admin' => $isAdmin
        ]);
    }



}
