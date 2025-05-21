<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Enum\ReservationStatut;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?User $utilisateur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateReservation = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureReservation = null;

    #[ORM\Column(type: 'string', enumType: ReservationStatut::class)]
    private ?ReservationStatut $statut = null;    

    /**
     * @var Collection<int, ReservationPrestation>
     */
    #[ORM\OneToMany(targetEntity: ReservationPrestation::class, mappedBy: 'reservation')]
    private Collection $reservationPrestations;

    public function __construct()
    {
        $this->reservationPrestations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getDateReservation(): ?\DateTime
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTime $dateReservation): static
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getHeureReservation(): ?\DateTime
    {
        return $this->heureReservation;
    }

    public function setHeureReservation(\DateTime $heureReservation): static
    {
        $this->heureReservation = $heureReservation;

        return $this;
    }

    public function getStatut(): ?ReservationStatut
    {
        return $this->statut;
    }

    public function setStatut(ReservationStatut $statut): static
    {
        $this->statut = $statut;

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
            $reservationPrestation->setReservation($this);
        }

        return $this;
    }

    public function removeReservationPrestation(ReservationPrestation $reservationPrestation): static
    {
        if ($this->reservationPrestations->removeElement($reservationPrestation)) {
            // set the owning side to null (unless already changed)
            if ($reservationPrestation->getReservation() === $this) {
                $reservationPrestation->setReservation(null);
            }
        }

        return $this;
    }
}
