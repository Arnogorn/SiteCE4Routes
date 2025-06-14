<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
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
    private ?string $titre = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Moniteur $moniteur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'La durée ne peut contenir que des chiffres.'
    )]
    private ?int $duree = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le nombre maximum d\'inscriptions ne peut contenir que des chiffres.'
    )]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le prix ne peut contenir que des chiffres.'
    )]
    private ?int $prix = null;

    /**
     * @var Collection<int, Niveau>
     */
    #[ORM\ManyToMany(targetEntity: Niveau::class, inversedBy: 'sorties')]
    private Collection $niveauxAdmis;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[\s\S]{0,500}$/',
        message: 'Les informations ne peuvent dépasser 500 caractères.'
    )]
    private ?string $infos = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: false)]
    private ?bool $isPublished = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sorties')]
    #[ORM\JoinTable(name: 'sortie_user')]
    private Collection $participants;

    #[ORM\ManyToMany(targetEntity: MembreFamille::class, inversedBy: 'sorties')]
    private Collection $membresFamilleInscrits;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeSortie $typeSortie = null;

    public function __construct()
    {
        $this->niveauxAdmis = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->membresFamilleInscrits = new ArrayCollection();
    }

    /// Relation Membre Sortie ///

    public function getMembresFamilleInscrits(): Collection
    {
        return $this->membresFamilleInscrits;
    }

    public function addMembresFamilleInscrit(MembreFamille $membre): static
    {
        if (!$this->membresFamilleInscrits->contains($membre)) {
            $this->membresFamilleInscrits->add($membre);
            $membre->addSortie($this);
        }

        return $this;
    }

    public function removeMembresFamilleInscrit(MembreFamille $membre): static
    {
        if ($this->membresFamilleInscrits->removeElement($membre)) {
            $membre->removeSortie($this);
        }

        return $this;
    }

    /// Fin de la relation Membre Sortie ///


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

    public function getMoniteur(): ?Moniteur
    {
        return $this->moniteur;
    }

    public function setMoniteur(?Moniteur $moniteur): static
    {
        $this->moniteur = $moniteur;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Niveau>
     */
    public function getNiveauxAdmis(): Collection
    {
        return $this->niveauxAdmis;
    }

    public function addNiveauxAdmi(Niveau $niveauxAdmi): static
    {
        if (!$this->niveauxAdmis->contains($niveauxAdmi)) {
            $this->niveauxAdmis->add($niveauxAdmi);
        }

        return $this;
    }

    public function removeNiveauxAdmi(Niveau $niveauxAdmi): static
    {
        $this->niveauxAdmis->removeElement($niveauxAdmi);

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

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addSortie($this);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSortie($this);
        }

        return $this;
    }

    public function getInscriptions(): Collection
    {
        return new ArrayCollection(array_merge(
            $this->participants->toArray(),
            $this->membresFamilleInscrits->toArray()
        ));
    }
//    #[Assert\Callback]
//    public function validateDates(ExecutionContextInterface $context): void
//    {
//        if ($this->dateLimiteInscription && $this->date) {
//            if ($this->dateLimiteInscription > $this->date) {
//                $context->buildViolation('La date limite d\'inscription doit être antérieure à la date de début de la sortie.')
//                    ->atPath('dateLimiteInscription')
//                    ->addViolation();
//            }
//        }
//    }

    public function getNombreInscritsTotal(): int
    {
        return $this->participants->count() + $this->membresFamilleInscrits->count();
    }

    public function getTypeSortie(): ?TypeSortie
    {
        return $this->typeSortie;
    }

    public function setTypeSortie(?TypeSortie $typeSortie): static
    {
        $this->typeSortie = $typeSortie;

        return $this;
    }
}
