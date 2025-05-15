<?php

namespace App\Entity;

use App\Repository\FamilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FamilleRepository::class)]
class Famille
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, MembreFamille>
     */
    #[ORM\OneToMany(targetEntity: MembreFamille::class, mappedBy: 'famille', orphanRemoval: true)]
    private Collection $membre;

    #[ORM\OneToOne(mappedBy: 'famille', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->membre = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, MembreFamille>
     */
    public function getMembre(): Collection
    {
        return $this->membre;
    }

    public function addMembre(MembreFamille $membre): static
    {
        if (!$this->membre->contains($membre)) {
            $this->membre->add($membre);
            $membre->setFamille($this);
        }

        return $this;
    }

    public function removeMembre(MembreFamille $membre): static
    {
        if ($this->membre->removeElement($membre)) {
            // set the owning side to null (unless already changed)
            if ($membre->getFamille() === $this) {
                $membre->setFamille(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // éviter boucle infinie
        if ($this->user === $user) {
            return $this;
        }

        $this->user = $user;

        if ($user !== null && $user->getFamille() !== $this) {
            $user->setFamille($this);
        }

        return $this;
    }
}
