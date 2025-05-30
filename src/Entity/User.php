<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte associé à cette adresse email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide')]
    #[Assert\Length(max: 180, maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'Le prénom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire')]
    #[Assert\LessThan('today', message: 'La date de naissance doit être antérieure à aujourd\'hui')]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone ne peut pas être vide')]
    #[Assert\Regex(
        pattern: '/^[0-9]{10}$/',
        message: 'Le numéro de téléphone doit contenir 10 chiffres pour un numéro français ou commencer par 00(indicateur pays) pour un numéro étranger'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telPersContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $medecinTraitant = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telMedecin = null;

    #[ORM\Column]
    private ?bool $droitImage = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infos = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Niveau $niveau = null;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Famille $famille = null;

    /**
     * @var Collection<int, Cheval>
     */
    #[ORM\OneToMany(targetEntity: Cheval::class, mappedBy: 'user')]
    private Collection $cheval;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participants')]
    private Collection $sorties;

    public function __construct()
    {
        $this->cheval = new ArrayCollection();
        $this->sorties = new ArrayCollection();

        // Création automatique d'une famille vide
        $famille = new Famille();
        $famille->setUser($this);
        $this->setFamille($famille);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

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

    public function getTelPersContact(): ?string
    {
        return $this->telPersContact;
    }

    public function setTelPersContact(?string $telPersContact): static
    {
        $this->telPersContact = $telPersContact;

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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getFamille(): ?Famille
    {
        return $this->famille;
    }

    public function setFamille(?Famille $famille): static
    {
        if ($this->famille === $famille) {
            return $this;
        }

        $this->famille = $famille;

        if ($famille !== null && $famille->getUser() !== $this) {
            $famille->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Cheval>
     */
    public function getCheval(): Collection
    {
        return $this->cheval;
    }

    public function addCheval(Cheval $cheval): static
    {
        if (!$this->cheval->contains($cheval)) {
            $this->cheval->add($cheval);
            $cheval->setUser($this);
        }

        return $this;
    }

    public function removeCheval(Cheval $cheval): static
    {
        if ($this->cheval->removeElement($cheval)) {
            // set the owning side to null (unless already changed)
            if ($cheval->getUser() === $this) {
                $cheval->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

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
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): static
    {
        if ($this->sorties->removeElement($sortie)) {
            $sortie->removeParticipant($this);
        }

        return $this;
    }
}
