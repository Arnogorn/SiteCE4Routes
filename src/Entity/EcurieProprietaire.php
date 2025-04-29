<?php

namespace App\Entity;

use App\Repository\EcurieProprietaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcurieProprietaireRepository::class)]
class EcurieProprietaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $prestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $prix = null;

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

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}
