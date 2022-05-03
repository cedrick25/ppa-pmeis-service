<?php

namespace App\Entity;

use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VpaAssociationInitiatedActivitiesRepository::class)
 */
class VpaAssociationInitiatedActivities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $serviceRendered;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $venueDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $venue_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceRendered(): ?string
    {
        return $this->serviceRendered;
    }

    public function setServiceRendered(string $serviceRendered): self
    {
        $this->serviceRendered = $serviceRendered;

        return $this;
    }

    public function getVenueDate(): ?\DateTimeImmutable
    {
        return $this->venueDate;
    }

    public function setVenueDate(\DateTimeImmutable $venueDate): self
    {
        $this->venueDate = $venueDate;

        return $this;
    }

    public function getVenueId(): ?int
    {
        return $this->venue_id;
    }

    public function setVenueId(int $venue_id): self
    {
        $this->venue_id = $venue_id;

        return $this;
    }
}
