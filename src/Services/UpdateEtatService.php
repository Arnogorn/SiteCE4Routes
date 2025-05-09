<?php

namespace App\Services;

use App\Entity\Sortie;
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
        // Récupérer les états par libellé au lieu d'utiliser des indices
        $etatPassee = $this->etatRepository->findOneBy(['libelle' => 'Passée']);
        $etatEnCours = $this->etatRepository->findOneBy(['libelle' => 'En cours']);
        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);

        // Vérifier que tous les états existent
        if (!$etatPassee || !$etatEnCours || !$etatCloturee) {
            throw new \RuntimeException('Certains états nécessaires n\'existent pas dans la base de données');
        }

        foreach ($sorties as $sortie) {
            // On prépare les variables
            $now = new \DateTime('now');
            $cloture = $sortie->getDateLimiteInscription();
            $debut = $sortie->getDate(); // Je suppose que c'est getDate() et non getDateHeureDebut()
            $duree = $sortie->getDuree();

            // Calculer la date de fin de la sortie
            $fin = clone($debut);
            $fin = $fin->add(new DateInterval('PT' . $duree . 'M'));

            $etatActuel = $sortie->getEtat();
            $etatHasChanged = false;

            // Vérification des conditions et mise à jour des états
            if ($fin <= $now) {
                // La sortie est terminée
                if ($etatActuel->getLibelle() !== 'Passée') {
                    $sortie->setEtat($etatPassee);
                    $etatHasChanged = true;
                }
            } elseif ($debut <= $now) {
                // La sortie est en cours
                if ($etatActuel->getLibelle() !== 'En cours') {
                    $sortie->setEtat($etatEnCours);
                    $etatHasChanged = true;
                }
            } elseif ($cloture <= $now) {
                // La période d'inscription est terminée
                if ($etatActuel->getLibelle() !== 'Clôturée') {
                    $sortie->setEtat($etatCloturee);
                    $etatHasChanged = true;
                }
            }

            if ($etatHasChanged) {
                $this->entityManager->persist($sortie);
            }
        }

        $this->entityManager->flush();
    }
}