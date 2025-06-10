<?php

namespace App\Entity;

use App\Repository\MembreFamilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MembreFamilleRepository::class)]
class MembreFamille
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

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,255}$/',
        message: 'Les allergies ne peuvent dépasser 255 caractères.'
    )]
    private ?string $allergies = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]*$/u',
        message: 'Le nom du médecin ne peut contenir que des lettres, espaces, tirets et apostrophes.'
    )]
    private ?string $medecinTraitant = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\d+\s\-]+$/',
        message: 'Le numéro de téléphone du médecin ne peut contenir que des chiffres, espaces, + et tirets.'
    )]
    private ?string $telMedecin = null;

    #[ORM\Column]
    private ?bool $droitImage = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Niveau $niveau = null;

    #[ORM\ManyToOne(inversedBy: 'membre')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Famille $famille = null;

    #[ORM\ManyToMany(mappedBy: 'membresFamilleInscrits', targetEntity: Sortie::class)]
    private Collection $sorties;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $NoLicence = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    /// Relation Membre Sortie ///
    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): static
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties->add($sortie);
            $sortie->addMembresFamilleInscrit($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): static
    {
        if ($this->sorties->removeElement($sortie)) {
            $sortie->removeMembresFamilleInscrit($this);
        }

        return $this;
    }

    /// Fin de la relation Membre Sortie ///

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

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeImmutable $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getMedecinTraitant(): ?string
    {
        return $this->medecinTraitant;
    }

    public function setMedecinTraitant(?string $medecinTraitant): static
    {
        $this->medecinTraitant = $medecinTraitant;

        return $this;
    }

    public function getTelMedecin(): ?string
    {
        return $this->telMedecin;
    }

    public function setTelMedecin(?string $telMedecin): static
    {
        $this->telMedecin = $telMedecin;

        return $this;
    }

    public function isDroitImage(): ?bool
    {
        return $this->droitImage;
    }

    public function setDroitImage(bool $droitImage): static
    {
        $this->droitImage = $droitImage;

        return $this;
    }

    public function getFamille(): ?Famille
    {
        return $this->famille;
    }

    public function setFamille(?Famille $famille): static
    {
        $this->famille = $famille;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getNoLicence(): ?string
    {
        return $this->NoLicence;
    }

    public function setNoLicence(?string $NoLicence): static
    {
        $this->NoLicence = $NoLicence;

        return $this;
    }
}
