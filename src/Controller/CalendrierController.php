<?php

namespace App\Controller;

use App\Entity\Calendrier;
use App\Form\CalendrierType;
use App\Repository\CalendrierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/calendrier')]
class CalendrierController extends AbstractController
{
    #[Route('/', name: 'app_calendrier_index', methods: ['GET'])]
    public function index(CalendrierRepository $calendrierRepository): Response
    {
        // Jours de la semaine
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        // Heures de la journée
        $heures = [];
        for ($h = 8; $h <= 20; $h++) {
            $heures[] = "$h:30";
        }

        // Préparer la grille et récupérer les événements
        $grille = [];
        foreach ($jours as $jour) {
            $grille[$jour] = [];

            // Récupérer tous les événements pour ce jour
            $evenementsDuJour = $calendrierRepository->findByJour($jour);

            // Initialiser les cellules de la grille
            foreach ($heures as $heure) {
                $grille[$jour][$heure] = [
                    'type' => 'vide',
                    'evenement' => null,
                    'rowspan' => 0,
                    'skip' => false
                ];
            }

            // Remplir la grille avec les événements
            foreach ($evenementsDuJour as $evenement) {
                $heureDebut = $evenement->getHeureDebut();
                $heureFin = $evenement->getHeureFin();

                // S'assurer que l'heure de début est dans notre liste d'heures
                if (!in_array($heureDebut, $heures)) {
                    continue;
                }

                // Extraire les heures pour le calcul
                list($heureDebutH, $heureDebutM) = explode(':', $heureDebut);
                list($heureFinH, $heureFinM) = explode(':', $heureFin);

                // Convertir en minutes pour faciliter les calculs
                $debutMinutes = (int)$heureDebutH * 60 + (int)$heureDebutM;
                $finMinutes = (int)$heureFinH * 60 + (int)$heureFinM;

                // Calculer la durée en nombre de créneaux d'une heure
                $dureeMinutes = $finMinutes - $debutMinutes;
                $nombreCreneaux = ceil($dureeMinutes / 60);

                // S'assurer qu'il y a au moins 1 créneau
                if ($nombreCreneaux < 1) {
                    $nombreCreneaux = 1;
                }

                // Marquer le créneau de début avec l'événement
                $grille[$jour][$heureDebut] = [
                    'type' => 'debut',
                    'evenement' => $evenement,
                    'rowspan' => $nombreCreneaux,
                    'skip' => false,
                    'contenu' => $evenement->getContenu()  // Ajout explicite du contenu
                ];

                // Marquer les créneaux suivants comme devant être ignorés
                $heureActuelle = (int)$heureDebutH;

                for ($i = 1; $i < $nombreCreneaux; $i++) {
                    $heureActuelle++;
                    if ($heureActuelle > 20) break; // Ne pas dépasser la dernière heure

                    $prochainCreneau = "$heureActuelle:30";
                    if (isset($grille[$jour][$prochainCreneau])) {
                        $grille[$jour][$prochainCreneau]['skip'] = true;
                    }
                }
            }
        }

        return $this->render('calendrier/index.html.twig', [
            'jours' => $jours,
            'heures' => $heures,
            'grille' => $grille,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/nouveau', name: 'app_calendrier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CalendrierRepository $calendrierRepository): Response
    {
        $calendrier = new Calendrier();

        // Récupérer les paramètres de l'URL s'ils existent
        $jour = $request->query->get('jour');
        $heureDebut = $request->query->get('heure'); // Format attendu: '8:30'

        // Définir les valeurs par défaut si elles sont fournies
        if ($jour) {
            $calendrier->setJour($jour);
        }

        if ($heureDebut) {
            $calendrier->setHeureDebut($heureDebut);

            // Calculer l'heure de fin par défaut (1 heure plus tard)
            list($h, $m) = explode(':', $heureDebut);
            $hFin = (int)$h + 1;
            if ($hFin <= 21) {
                $calendrier->setHeureFin("$hFin:30");
            } else {
                $calendrier->setHeureFin("21:30"); // Maximum
            }
        }

        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que l'heure de fin est après l'heure de début
            $heureDebut = $calendrier->getHeureDebut();
            $heureFin = $calendrier->getHeureFin();

            // Convertir en minutes pour faciliter la comparaison
            list($heureDebutH, $heureDebutM) = explode(':', $heureDebut);
            list($heureFinH, $heureFinM) = explode(':', $heureFin);

            $debutMinutes = (int)$heureDebutH * 60 + (int)$heureDebutM;
            $finMinutes = (int)$heureFinH * 60 + (int)$heureFinM;

            if ($finMinutes <= $debutMinutes) {
                $this->addFlash('error', 'L\'heure de fin doit être après l\'heure de début.');
            } else {
                // Vérifier les chevauchements
                $chevauchements = $this->verifierChevauchements(
                    $calendrierRepository,
                    $calendrier->getJour(),
                    $heureDebut,
                    $heureFin
                );

                if (count($chevauchements) > 0) {
                    $this->addFlash('error', 'Un événement existe déjà sur ce créneau horaire.');
                } else {
                    $entityManager->persist($calendrier);
                    $entityManager->flush();
                    $this->addFlash('success', 'Événement ajouté avec succès.');
                    return $this->redirectToRoute('app_calendrier_index');
                }
            }
        }

        return $this->render('calendrier/new.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/modifier', name: 'app_calendrier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Calendrier $calendrier, EntityManagerInterface $entityManager, CalendrierRepository $calendrierRepository): Response
    {
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que l'heure de fin est après l'heure de début
            $heureDebut = $calendrier->getHeureDebut();
            $heureFin = $calendrier->getHeureFin();

            // Convertir en minutes pour faciliter la comparaison
            list($heureDebutH, $heureDebutM) = explode(':', $heureDebut);
            list($heureFinH, $heureFinM) = explode(':', $heureFin);

            $debutMinutes = (int)$heureDebutH * 60 + (int)$heureDebutM;
            $finMinutes = (int)$heureFinH * 60 + (int)$heureFinM;

            if ($finMinutes <= $debutMinutes) {
                $this->addFlash('error', 'L\'heure de fin doit être après l\'heure de début.');
            } else {
                // Vérifier les chevauchements, en excluant l'événement actuel
                $chevauchements = $this->verifierChevauchements(
                    $calendrierRepository,
                    $calendrier->getJour(),
                    $heureDebut,
                    $heureFin,
                    $calendrier->getId()
                );

                if (count($chevauchements) > 0) {
                    $this->addFlash('error', 'Un événement existe déjà sur ce créneau horaire.');
                } else {
                    $entityManager->flush();
                    $this->addFlash('success', 'Événement mis à jour avec succès.');
                    return $this->redirectToRoute('app_calendrier_index');
                }
            }
        }

        return $this->render('calendrier/edit.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_calendrier_delete', methods: ['POST'])]
    public function delete(Request $request, Calendrier $calendrier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calendrier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($calendrier);
            $entityManager->flush();
            $this->addFlash('success', 'Événement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_calendrier_index');
    }

    /**
     * Vérifie s'il y a des chevauchements pour un créneau donné
     */
    /**
     * Vérifie s'il y a des chevauchements pour un créneau donné
     */
    private function verifierChevauchements(
        CalendrierRepository $repository,
        string $jour,
        string $heureDebut,
        string $heureFin,
        ?int $excludeId = null
    ): array {
        return $repository->findOverlappingEvents($jour, $heureDebut, $heureFin, $excludeId);
    }

}