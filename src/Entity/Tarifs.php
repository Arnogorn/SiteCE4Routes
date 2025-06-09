<?php

namespace App\Entity;

use App\Repository\TarifsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TarifsRepository::class)]
class Tarifs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La catégorie est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ0-9_\-]+$/u',
        message: 'La catégorie ne peut contenir que des lettres, chiffres, underscores et tirets.'
    )]
    #[Assert\Length(
        max: 100,
        maxMessage: 'La catégorie ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le prix ne peut contenir que des chiffres.'
    )]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Le prix ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?string
    {
        return match($this->categorie) {
            'forfait' => 'Forfait',
            'a_la_carte' => 'À la carte',
            'balades' => 'Balades',
            'licences' => 'Licences',
            'adhesions' => 'Adhésions',
            default => ucfirst($this->categorie ?? '')
        };
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
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

    public function getCategorieRaw(): ?string
    {
        return $this->categorie;
    }
}
