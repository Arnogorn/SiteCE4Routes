<?php

namespace App\Controller;

use App\Service\NotificationService;
use App\Entity\Sortie;
use App\Entity\MembreFamille;
use App\Entity\User;
use App\Form\SortieType;
use App\Form\SortieFiltreType;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Entity\Paiement;
use App\Entity\HistoriquePaiement;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\UpdateEtatService;
use App\Service\InscriptionService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    private NotificationService $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->notifier = $notifier;
    }
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, Security $security, UpdateEtatService $updateEtatService): Response
    {
        $user = $security->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        $form = $this->createForm(SortieFiltreType::class);
        $form->handleRequest($request);

        // Récupération des critères
        $criteria = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];

        // Récupération du tri via query string
        $tri = $request->query->get('tri');
        if ($tri && in_array($tri, ['asc', 'desc'])) {
            $criteria['tri'] = $tri;
        }

        // Définition des états à afficher
        if ($isAdmin) {
            $etatsLibelles = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée', 'Archivée'];
        } else {
            $etatsLibelles = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée'];
        }

        // Si un état spécifique est sélectionné dans le filtre
        if (!empty($criteria['etat'])) {
            $etatLibelle = $criteria['etat']->getLibelle();
            if ($isAdmin || $etatLibelle !== 'Archivée') {
                $etatsLibelles = [$etatLibelle];
            }
            unset($criteria['etat']); // Retirer de criteria car déjà géré
        }

        // Récupération des sorties avec une seule requête
        // et une méthode qui utilise des jointures et sélectionne toutes les relations
        $sorties = $sortieRepository->findSortiesByEtatsAndCriteria($etatsLibelles, $criteria);

        // Mise à jour des états
        $updateEtatService->updateEtat($sorties);

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView(),
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ajouter', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($sortie->getDate() !== null) {
                $dateLimite = $sortie->getDate()->sub(new DateInterval('P2D'));
                $sortie->setDateLimiteInscription($dateLimite);
            }
            if ($sortie->isPublished() === true) {
                // Récupérer l'état "Ouvert" depuis la base de données
                $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouverte']);


                if ($etatOuvert) {
                    $sortie->setEtat($etatOuvert);
                } else {
                    // Gestion d'erreur si l'état n'existe pas
                    $this->addFlash('danger', 'Impossible de trouver l\'état "Ouvert". Veuillez vérifier votre configuration.');

                }
            } else {
                // Si non publiée, définir un état par défaut, par exemple "Créée"
                $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
                if ($etatCree) {
                    $sortie->setEtat($etatCree);
                }
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {


        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($sortie->getDate() !== null) {
                $dateLimite = $sortie->getDate()->sub(new DateInterval('P2D'));
                $sortie->setDateLimiteInscription($dateLimite);
            }
            if ($sortie->isPublished() === true) {
                // Récupérer l'état "Ouvert" depuis la base de données
                $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouverte']);

                if ($etatOuvert) {
                $sortie->setEtat($etatOuvert);
            } else {
                // Gestion d'erreur si l'état n'existe pas
                $this->addFlash('danger', 'Impossible de trouver l\'état "Ouvert". Veuillez vérifier votre configuration.');

            }
        } else {
            // Si non publiée, définir un état par défaut, par exemple "Créée"
            $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
            if ($etatCree) {
                $sortie->setEtat($etatCree);
            }
        }
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription')]
    public function inscription(Sortie $sortie, EntityManagerInterface $em, Security $security): Response
    {

        //TODO gérer les conditions d'inscription aux sorties (en fonction de l'état)
        /** @var User $user */
        $user = $security->getUser();

        if (!in_array($sortie->getEtat()->getLibelle(), ['Ouverte'])) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous inscrire à une sortie non ouverte.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        if ($sortie->getDate() < new \DateTime()) {
            $this->addFlash('danger', 'Cette sortie est déjà terminée, vous ne pouvez plus vous inscrire.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifie si déjà inscrit
        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        } elseif ($sortie->getNombreInscritsTotal() >= $sortie->getNbInscriptionMax()) {
            $this->addFlash('danger', 'Cette sortie est complète, vous ne pouvez pas vous inscrire.');
        } elseif ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('danger', 'La date limite d\'inscription est dépassée.');
        } else {
            $sortie->addParticipant($user);
            $em->flush();
            $this->addFlash('success', 'Inscription réussie !');
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desinscription', name: 'sortie_desinscription')]
    public function desinscription(
        Sortie $sortie,
        Security $security,
        InscriptionService $inscriptionService
    ): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous désinscrire.');
        }

        try {
            $inscriptionService->unregisterParticipant($sortie, $user);
            $this->notifier->sendDesinscriptionMail($user, null, $sortie, null);
            $this->addFlash('success', 'Vous vous êtes désinscrit de la sortie. Un e-mail de confirmation vous a été envoyé.');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desinscription-membre/{membreId}', name: 'sortie_desinscription_membre', methods: ['POST'])]
    public function desinscriptionMembre(
        Sortie $sortie,
        MembreFamille $membre,
        Security $security,
        InscriptionService $inscriptionService
    ): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour désinscrire un membre.');
        }

        try {
            $inscriptionService->unregisterParticipant($sortie, $user, $membre);
            $this->notifier->sendDesinscriptionMail($user, $membre, $sortie, null);
            $this->addFlash('success', 'Le membre a bien été désinscrit de la sortie et remboursé. Un e-mail de confirmation a été envoyé.');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/sortie/publier/{id}', name:'sortie_publier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function publierSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
    ){



        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);


        if (!$etatOuverte) {
            $this->addFlash('error', 'État "Ouverte" introuvable.');
            return $this->redirectToRoute('sortie');
        }

        // Changer l'état de la sortie en Ouverte
        $sortie->setIsPublished(true);


        // Save changes
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'La sortie a été publiée avec succès !');

        // Redirect to sortie details page
        return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
    }

    /// Inscription de MembreFamille ///
    #[Route('/{id}/inscription-famille', name: 'sortie_inscription_famille', methods: ['GET', 'POST'])]
    public function inscriptionFamille(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        StripeClient $stripe,
        InscriptionService $inscriptionService
    ): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user || !$user->getFamille()) {
            throw $this->createAccessDeniedException('Aucune famille trouvée.');
        }

        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('danger', 'La date limite d\'inscription est dépassée pour cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $membres = $user->getFamille()->getMembre();

        if ($request->isMethod('POST')) {
            $ids = $request->request->all('membres'); // tableau d’IDs cochés

            $membresACocher = [];
            foreach ($ids as $id) {
                foreach ($membres as $membre) {
                    if ($membre->getId() == $id && $membre->getFamille() === $user->getFamille()) {
                        $membresACocher[] = $membre;
                        break;
                    }
                }
            }

            $dejaInscrits = array_filter($membresACocher, function ($m) use ($sortie) {
                return $sortie->getMembresFamilleInscrits()->contains($m);
            });

            $aInscrire = count($membresACocher) - count($dejaInscrits);
            $placesDispo = $sortie->getNbInscriptionMax() - (
                count($sortie->getParticipants()) + count($sortie->getMembresFamilleInscrits())
            );

            if ($aInscrire > $placesDispo) {
                $this->addFlash('danger', 'Il ne reste que '.$placesDispo.' place(s) disponible(s). Veuillez ajuster votre sélection.');
                return $this->redirectToRoute('sortie_inscription_famille', ['id' => $sortie->getId()]);
            }

            $placesRestantes = $placesDispo;

            foreach ($membres as $membre) {
                if ($membre->getFamille() !== $user->getFamille()) {
                    continue;
                }
                $estInscrit = $sortie->getMembresFamilleInscrits()->contains($membre);
                $seraInscrit = in_array($membre->getId(), $ids);

                if ($seraInscrit) {
                    if (!$estInscrit && $placesRestantes > 0) {
                        $this->addFlash('success', 'Membres inscrits avec succès.');
                        $sortie->addMembresFamilleInscrit($membre);
                        $membre->addSortie($sortie);
                        $placesRestantes--;
                    }
                } else if ($sortie->getDateLimiteInscription() < new \DateTime()) {
                    $this->addFlash('danger', 'Vous ne pouvez plus vous désinscrire de la sortie.');
                } elseif ($estInscrit) {
                    try {
                        $inscriptionService->unregisterParticipant($sortie, $user, $membre);
                        $this->notifier->sendDesinscriptionMail($user, $membre, $sortie, null);
                        $this->addFlash('success', 'Le membre de famille a bien été désinscrit et remboursé. Un e-mail de confirmation a été envoyé.');
                    } catch (\Exception $e) {
                        $this->addFlash('danger', $e->getMessage());
                    }
                }
            }

            // Nouvelle vérification après désinscriptions et inscriptions tentées
            if ($placesRestantes < 0) {
                $this->addFlash('danger', 'La sortie est complète, impossible d\'inscrire tous les membres sélectionnés.');
                return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
            }

            $em->flush();

            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/inscription_famille.html.twig', [
            'sortie' => $sortie,
            'membres' => $membres,
            'membresDejaInscrits' => $sortie->getMembresFamilleInscrits(),
        ]);
    }

    #[Route('/{id}/gestion-inscriptions', name: 'sortie_gestion_inscriptions', methods: ['GET', 'POST'])]
    public function gestionInscriptions(
        Sortie $sortie,
        Request $request,
        Security $security,
        InscriptionService $inscriptionService
    ): Response {
        /** @var User $user */
        $user = $security->getUser();
        $famille = $user->getFamille()?->getMembre()->toArray() ?? [];

        // Build choices: user + members
        $choices = [
            $user->getId() => $user->getPrenom() . ' ' . $user->getNom()
        ];
        foreach ($famille as $membre) {
            $choices[$membre->getId()] = $membre->getPrenom() . ' ' . $membre->getNom();
        }

        // Initial selected IDs
        $initial = [];
        if ($sortie->getParticipants()->contains($user)) {
            $initial[] = $user->getId();
        }
        foreach ($sortie->getMembresFamilleInscrits() as $membre) {
            $initial[] = $membre->getId();
        }

        // Create form
        $form = $this->createFormBuilder(['participants' => $initial])
            ->add('participants', ChoiceType::class, [
                'choices'  => $choices,
                'expanded' => true,
                'multiple' => true,
                'data'     => $initial,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selected = $form->getData()['participants'];

            // Handle user registration/unregistration
            $isUserSelected = in_array($user->getId(), $selected, true);
            $isUserEnrolled = $sortie->getParticipants()->contains($user);
            // Si l'utilisateur est déjà inscrit et que son ID est dans la sélection, on empêche une nouvelle inscription
            if ($isUserSelected && $isUserEnrolled) {
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
                return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
            }
            if ($isUserSelected && !$isUserEnrolled) {
                return $this->redirect($inscriptionService->startCheckout($sortie, $user, [], true));
            } elseif (!$isUserSelected && $isUserEnrolled) {
                $inscriptionService->unregisterParticipant($sortie, $user);
            }

            // Handle family members
            $toRegister = [];
            foreach ($famille as $membre) {
                $selectedMember = in_array($membre->getId(), $selected, true);
                $isMemberEnrolled = $sortie->getMembresFamilleInscrits()->contains($membre);
                // Si le membre est déjà inscrit et est toujours sélectionné, on empêche une nouvelle inscription
                if ($selectedMember && $isMemberEnrolled) {
                    $this->addFlash('warning', sprintf('Le membre « %s » est déjà inscrit à cette sortie.', $membre->getPrenom()));
                    return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
                }
                if ($selectedMember && !$isMemberEnrolled) {
                    $toRegister[] = $membre;
                } elseif (!$selectedMember && $isMemberEnrolled) {
                    $inscriptionService->unregisterParticipant($sortie, $user, $membre);
                }
            }
            if (!empty($toRegister)) {
                return $this->redirect($inscriptionService->startCheckout($sortie, $user, $toRegister, false));
            }

            $this->addFlash('success', 'Mise à jour des inscriptions effectuée.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/gestion_inscriptions.html.twig', [
            'form'   => $form->createView(),
            'sortie' => $sortie,
        ]);
    }
}
