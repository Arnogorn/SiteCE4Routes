<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\'\-]+$/u',
        message: 'Le titre ne peut contenir que des lettres, chiffres, espaces, tirets et apostrophes.'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le titre ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $titre = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le prix du transport ne peut contenir que des chiffres.'
    )]
    private ?int $prixTransport = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le prix de l\'épreuve ne peut contenir que des chiffres.'
    )]
    private ?int $prixEpreuve = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,500}$/',
        message: 'La description ne peut dépasser 500 caractères.'
    )]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getPrixTransport(): ?int
    {
        return $this->prixTransport;
    }

    public function setPrixTransport(?int $prixTransport): static
    {
        $this->prixTransport = $prixTransport;

        return $this;
    }

    public function getPrixEpreuve(): ?int
    {
        return $this->prixEpreuve;
    }

    public function setPrixEpreuve(?int $prixEpreuve): static
    {
        $this->prixEpreuve = $prixEpreuve;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
