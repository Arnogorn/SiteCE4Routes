<?php

namespace App\Entity;

use App\Repository\EcurieProprietaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EcurieProprietaireRepository::class)]
class EcurieProprietaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La prestation est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\'\-]+$/u',
        message: 'La prestation ne peut contenir que des lettres, chiffres, espaces, tirets et apostrophes.'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La prestation ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $prestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,500}$/',
        message: 'La description ne peut dépasser 500 caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le prix ne peut contenir que des chiffres.'
    )]
    private ?string $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrestation(): ?string
    {
        return $this->prestation;
    }

    public function setPrestation(string $prestation): static
    {
        $this->prestation = $prestation;

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

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}
