<?php

namespace App\Entity;

use App\Repository\ChevalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChevalRepository::class)]
class Cheval
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
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]*$/u',
        message: 'Le nom du père ne peut contenir que des lettres, espaces, tirets et apostrophes.'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom du père ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $nomPere = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]*$/u',
        message: 'Le nom de la mère ne peut contenir que des lettres, espaces, tirets et apostrophes.'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom de la mère ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $nomMere = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\'\-]*$/u',
        message: 'Le lieu de naissance ne peut contenir que des lettres, chiffres, espaces, apostrophes et tirets.'
    )]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,255}$/',
        message: 'Le nom de la photo ne peut dépasser 255 caractères.'
    )]
    private ?string $photo = null;

    #[ORM\Column]
    private ?bool $appartientEcurie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,500}$/',
        message: 'Les informations ne peuvent dépasser 500 caractères.'
    )]
    private ?string $infos = null;

    #[ORM\Column]
    private ?bool $aVendre = null;

    #[ORM\Column]
    private ?bool $vendu = null;

    #[ORM\ManyToOne(inversedBy: 'cheval')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sexe $sexe = null;

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

    public function getNomPere(): ?string
    {
        return $this->nomPere;
    }

    public function setNomPere(?string $nomPere): static
    {
        $this->nomPere = $nomPere;

        return $this;
    }

    public function getNomMere(): ?string
    {
        return $this->nomMere;
    }

    public function setNomMere(?string $nomMere): static
    {
        $this->nomMere = $nomMere;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function isAppartientEcurie(): ?bool
    {
        return $this->appartientEcurie;
    }

    public function setAppartientEcurie(bool $appartientEcurie): static
    {
        $this->appartientEcurie = $appartientEcurie;

        return $this;
    }

    public function getInfos(): ?string
    {
        return $this->infos;
    }

    public function setInfos(?string $infos): static
    {
        $this->infos = $infos;

        return $this;
    }

    public function isAVendre(): ?bool
    {
        return $this->aVendre;
    }

    public function setAVendre(bool $aVendre): static
    {
        $this->aVendre = $aVendre;

        return $this;
    }

    public function isVendu(): ?bool
    {
        return $this->vendu;
    }

    public function setVendu(bool $vendu): static
    {
        $this->vendu = $vendu;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSexe(): ?Sexe
    {
        return $this->sexe;
    }

    public function setSexe(?Sexe $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }
}
