<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class VolunteerSupervisions
{
    /**
     * @param int[] $clientIds
     */
    public function __construct(
        private int $volunteerId,
        private array $clientIds,
        private int $servicesRenderedId,
        private ?string $communityResourcesTapped,
        private ?string $assistanceReceived,
        private ?string $remarks,
        private ?int $fieldOfficeId,
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
    public function getVolunteerId(): int
    {
        return $this->volunteerId;
    }

    /**
     * @return int[]
     */
    public function getClientIds(): array
    {
        return $this->clientIds;
    }

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
     * @return string|null
     */
    public function getCommunityResourcesTapped(): ?string
    {
        return $this->communityResourcesTapped;
    }

    /**
     * @return string|null
     */
    public function getAssistanceReceived(): ?string
    {
        return $this->assistanceReceived;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int|null
     */
    public function getFieldOfficeId(): ?int
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