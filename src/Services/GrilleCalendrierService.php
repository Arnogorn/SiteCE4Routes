<?php
// src/Service/GrilleCalendrierService.php

namespace App\Services;

use App\Repository\CalendrierRepository;

class GrilleCalendrierService
{
    private $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    private $heures = [];

    public function __construct()
    {
        // Initialiser les heures de la journée
        for ($h = 8; $h <= 20; $h++) {
            $this->heures[] = "$h:30";
        }
    }

    public function getJours(): array
    {
        return $this->jours;
    }

    public function getHeures(): array
    {
        return $this->heures;
    }

    public function creerGrille(CalendrierRepository $calendrierRepository): array
    {
        $grille = [];

        foreach ($this->jours as $jour) {
            $grille[$jour] = [];

            // Récupérer tous les événements pour ce jour
            $evenementsDuJour = $calendrierRepository->findByJour($jour);

            // Initialiser les cellules de la grille
            foreach ($this->heures as $heure) {
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
                if (!in_array($heureDebut, $this->heures)) {
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
                    'contenu' => $evenement->getContenu()
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

        return $grille;
    }
}