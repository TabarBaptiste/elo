<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $duree = null;

    /**
     * @var Collection<int, ReservationPrestation>
     */
    #[ORM\OneToMany(targetEntity: ReservationPrestation::class, mappedBy: 'prestation')]
    private Collection $reservationPrestations;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $formule = null;

    public function __construct()
    {
        $this->reservationPrestations = new ArrayCollection();
        $this->formule = 0;
    }

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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

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

    /**
     * @return Collection<int, ReservationPrestation>
     */
    public function getReservationPrestations(): Collection
    {
        return $this->reservationPrestations;
    }

    public function addReservationPrestation(ReservationPrestation $reservationPrestation): static
    {
        if (!$this->reservationPrestations->contains($reservationPrestation)) {
            $this->reservationPrestations->add($reservationPrestation);
            $reservationPrestation->setPrestation($this);
        }

        return $this;
    }

    public function removeReservationPrestation(ReservationPrestation $reservationPrestation): static
    {
        if ($this->reservationPrestations->removeElement($reservationPrestation)) {
            // set the owning side to null (unless already changed)
            if ($reservationPrestation->getPrestation() === $this) {
                $reservationPrestation->setPrestation(null);
            }
        }

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

    public function isFormule(): ?bool
    {
        return $this->formule;
    }

    public function setFormule(bool $formule): static
    {
        $this->formule = $formule;

        return $this;
    }
}
