<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/contact')]
final class ContactController extends AbstractController
{
    #[Route(name: 'app_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/nouveau', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Uploader $uploader): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la photo avec le service Uploader
            $picture = $form->get('photo')->getData();
            if ($picture) {
                $fileName = $uploader->save(
                    $picture,
                    $contact->getPrenom() . $contact->getNom(),
                    $this->getParameter('contact_picture_dir')
                );
                $contact->setPhoto($fileName);
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Le contact a été créé avec succès.');

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/modifier', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact, EntityManagerInterface $entityManager, Uploader $uploader): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la photo avec le service Uploader
            $picture = $form->get('photo')->getData();
            if ($picture) {
                // Supprimer l'ancienne photo si elle existait
                if ($contact->getPhoto()) {
                    $uploader->delete($contact->getPhoto(), $this->getParameter('contact_picture_dir'));
                }

                $fileName = $uploader->save(
                    $picture,
                    $contact->getPrenom() . $contact->getNom(),
                    $this->getParameter('contact_picture_dir')
                );
                $contact->setPhoto($fileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le contact a été modifié avec succès.');

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager, Uploader $uploader): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer la photo si elle existe avec le service Uploader
            if ($contact->getPhoto()) {
                $uploader->delete($contact->getPhoto(), $this->getParameter('contact_picture_dir'));
            }

            $entityManager->remove($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Le contact a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}