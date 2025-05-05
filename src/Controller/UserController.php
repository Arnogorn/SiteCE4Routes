<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/utilisateur')]
final class UserController extends AbstractController
{


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function showUtilisateur(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUtilisateur(Request $request, EntityManagerInterface $entityManager,  Uploader $uploader, UserPasswordHasherInterface $passwordHasher, Security $security ): Response
    {
        $user = $security->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la photo
            $picture = $form->get('photo')->getData();
            if ($picture) {
                // Supprimer la photo précédente si elle existait
                if ($user->getPhoto()) {
                    $uploader->delete($user->getPhoto(), $this->getParameter('participant_picture_dir'));
                }
                $fileName = $uploader->save($picture, $user->getEmail(), $this->getParameter('participant_picture_dir'));
                $user->setPhoto($fileName);
            }

            // Gérer la mise à jour du mot de passe
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    // Ajouter une erreur si les mots de passe ne correspondent pas
                    $form->get('confirm_password')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                } else {
                    // Hasher le mot de passe et mettre à jour l'utilisateur
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword); // Mettre à jour le mot de passe de l'utilisateur
                }
            }

            // Sauvegarder l'entité participant (si nécessaire)
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_test', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            //TODO modifier l'url de la page de redirection
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


}
