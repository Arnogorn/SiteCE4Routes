<?php

namespace App\Entity;

use App\Repository\CalendrierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CalendrierRepository::class)]
class Calendrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le contenu ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $contenu = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: 'Le jour est obligatoire.')]
    private ?string $jour = null;

    #[ORM\Column(length:5)]
    #[Assert\NotBlank(message: 'L\'heure de début est obligatoire.')]
    private ?string $heureDebut = null;

    #[ORM\Column(length:5)]
    #[Assert\NotBlank(message: 'L\'heure de fin est obligatoire.')]
    private ?string $heureFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHeureDebut(): ?string
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(string $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?string
    {
        return $this->heureFin;
    }

    public function setHeureFin(string $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }
}
