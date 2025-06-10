<?php

namespace App\Service;

use App\Repository\EtatRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEtatService
{
    public function __construct(private EtatRepository $etatRepository, private EntityManagerInterface $entityManager)
    {
    }

    public function updateEtat($sorties)
    {
        // Récupérer tous les états en une seule requête et les indexer par libellé
        $allEtats = $this->etatRepository->findAll();
        $etatsMap = [];
        foreach ($allEtats as $etat) {
            $etatsMap[$etat->getLibelle()] = $etat;
        }

        // Vérifier que tous les états nécessaires existent
        $requiredEtats = ['Passée', 'En cours', 'Clôturée', 'Ouverte', 'Créée', 'Archivée'];
        foreach ($requiredEtats as $libelle) {
            if (!isset($etatsMap[$libelle])) {
                throw new \RuntimeException("L'état '$libelle' n'existe pas dans la base de données");
            }
        }

        // Date actuelle pour comparaison
        $now = new \DateTime('now');

        // Date d'il y a 6 mois pour l'archivage
        $dateLimiteArchivage = (new \DateTime())->sub(new \DateInterval('P6M'));

        $hasChanges = false;

        foreach ($sorties as $sortie) {
            // On prépare les variables
            $debut = $sortie->getDate();
            $duree = $sortie->getDuree();
            $estPubliee = $sortie->isPublished();
            // Date de clôture des inscriptions : 30 minutes avant le début
            $cloture = (clone $debut)->sub(new DateInterval('PT30M'));

            // Calculer la date de fin de la sortie
            $fin = (clone $debut)->add(new DateInterval('PT' . $duree . 'M'));

            $etatActuel = $sortie->getEtat();
            $nouvelEtat = null;

            // Déterminer le nouvel état selon l'ordre de priorité chronologique
            // 1. Vérifier d'abord l'archivage (priorité absolue)
            if ($debut < $dateLimiteArchivage) {
                $nouvelEtat = $etatsMap['Archivée'];
            }
            // 2. Si pas publiée, elle reste en création
            elseif (!$estPubliee) {
                $nouvelEtat = $etatsMap['Créée'];
            }
            // 3. Si la sortie est terminée
            elseif ($fin <= $now) {
                $nouvelEtat = $etatsMap['Passée'];
            }
            // 4. Si la sortie a commencé mais n'est pas terminée
            elseif ($debut <= $now) {
                $nouvelEtat = $etatsMap['En cours'];
            }
            // 5. Si la date limite d'inscription est dépassée
            elseif ($cloture <= $now) {
                $nouvelEtat = $etatsMap['Clôturée'];
            }
            // 6. Si la sortie est publiée et les inscriptions sont ouvertes
            else {
                $nouvelEtat = $etatsMap['Ouverte'];
            }

            // Mettre à jour l'état seulement si nécessaire
            if ($nouvelEtat && $etatActuel->getLibelle() !== $nouvelEtat->getLibelle()) {
                $sortie->setEtat($nouvelEtat);
                $hasChanges = true;
            }
        }

        // Faire un seul flush pour toutes les modifications
        if ($hasChanges) {
            $this->entityManager->flush();
        }
    }
}