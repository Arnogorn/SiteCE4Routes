<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u',
        message: 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u',
        message: 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,255}$/',
        message: 'Le nom de la photo ne peut dépasser 255 caractères.'
    )]
    private ?string $photo = null;

    #[ORM\Column(length: 20)]
    #[Assert\Regex(
        pattern: '/^[\d+\s\-]+$/',
        message: 'Le numéro de téléphone ne peut contenir que des chiffres, espaces, + et tirets.'
    )]
    private ?string $tel = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,500}$/',
        message: 'La description ne peut dépasser 500 caractères.'
    )]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }
    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

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
}
