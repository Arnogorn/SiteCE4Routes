<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nom;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateInscription;

    #[ORM\ManyToOne(targetEntity: Paiement::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paiement $paiement = null;

    #[ORM\ManyToOne(targetEntity: Sortie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $inscritPar = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: MembreFamille::class)]
    private ?MembreFamille $membreFamille = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDateInscription(): \DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(Paiement $paiement): self
    {
        $this->paiement = $paiement;
        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(Sortie $sortie): self
    {
        $this->sortie = $sortie;
        return $this;
    }

    public function getInscritPar(): ?User
    {
        return $this->inscritPar;
    }

    public function setInscritPar(User $inscritPar): self
    {
        $this->inscritPar = $inscritPar;
        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getMembreFamille(): ?MembreFamille
    {
        return $this->membreFamille;
    }

    public function setMembreFamille(?MembreFamille $membreFamille): self
    {
        $this->membreFamille = $membreFamille;
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->dateInscription = new \DateTimeImmutable();
    }
}