<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class VpaAssociationInitiatedActivities
{
    public function __construct(
        private int $servicesRenderedId,
        private string $venueDate,
        private int $venueId,
        private int $volunteerId,
        private string $role,
        private ?string $crdResourcesTapped,
        private ?string $crdAssistanceReceived,
        private string $remarks,
        private int $fieldOfficeId,
        private int $quarterId,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getServicesRenderedId(): int
    {
        return $this->servicesRenderedId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getVenueDate(): string
    {
        return $this->venueDate;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getVenueId(): int
    {
        return $this->venueId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getVolunteerId(): int
    {
        return $this->volunteerId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string|null
     */
    public function getCrdResourcesTapped(): ?string
    {
        return $this->crdResourcesTapped;
    }

    /**
     * @return string|null
     */
    public function getCrdAssistanceReceived(): ?string
    {
        return $this->crdAssistanceReceived;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRemarks(): string
    {
        return $this->remarks;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getQuarterId(): int
    {
        return $this->quarterId;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}