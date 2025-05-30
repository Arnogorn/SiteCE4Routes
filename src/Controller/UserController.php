<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/utilisateur')]
final class UserController extends AbstractController
{


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function showUtilisateur(User $userToShow, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $currentUser = $security->getUser();

        // Vérifier que l'utilisateur connecté est soit le propriétaire du profil, soit un admin
        if ($currentUser->getId() !== $userToShow->getId() && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            // Accès refusé
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour consulter ce profil.');
            return $this->redirectToRoute('index'); // Redirection vers la page d'accueil ou une autre page appropriée
        }

        // À partir d'ici, l'accès est autorisé
        $hasPhoto = false;
        if ($userToShow->getPhoto() !== null && file_exists($this->getParameter('participant_picture_dir') . '/' . $userToShow->getPhoto())) {
            $hasPhoto = true;
        }

        return $this->render('user/show.html.twig', [
            'user' => $userToShow,
            'hasPhoto' => $hasPhoto
        ]);
    }


    #[Route('/{id}/modifier', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUtilisateur(Request $request, User $userToEdit, EntityManagerInterface $entityManager, Uploader $uploader, UserPasswordHasherInterface $passwordHasher, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $currentUser = $security->getUser();

        // Vérifier que l'utilisateur connecté est soit le propriétaire du profil, soit un admin
        if ($currentUser->getId() !== $userToEdit->getId() && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            // Accès refusé
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier ce profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $userToEdit->getId()]);
        }

        // À partir d'ici, l'accès est autorisé
        $form = $this->createForm(UserType::class, $userToEdit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la photo
            $picture = $form->get('photo')->getData();
            if ($picture) {
                // Supprimer la photo précédente si elle existait
                if ($userToEdit->getPhoto()) {
                    $uploader->delete($userToEdit->getPhoto(), $this->getParameter('participant_picture_dir'));
                }
                $fileName = $uploader->save($picture, $userToEdit->getEmail(), $this->getParameter('participant_picture_dir'));
                $userToEdit->setPhoto($fileName);
            }

            // Gérer la mise à jour du mot de passe
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    // Ajouter une erreur si les mots de passe ne correspondent pas
                    $form->get('confirm_password')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                    return $this->render('user/edit.html.twig', [
                        'user' => $userToEdit,
                        'form' => $form,
                    ]);
                } else {
                    // Hasher le mot de passe et mettre à jour l'utilisateur
                    $hashedPassword = $passwordHasher->hashPassword($userToEdit, $newPassword);
                    $userToEdit->setPassword($hashedPassword); // Mettre à jour le mot de passe de l'utilisateur
                }
            }

            if(!in_array("ROLE_ADMIN", $userToEdit->getRoles())){
                $userToEdit->setActif(true);
            }

            // Sauvegarder l'entité
            $entityManager->flush();

            $this->addFlash('success', 'Le profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_user_show', ['id' => $userToEdit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $userToEdit,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/',name: 'app_user_index', methods: ['GET'])]
    public function indexUtilisateur(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function deleteUtilisateur(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


}
