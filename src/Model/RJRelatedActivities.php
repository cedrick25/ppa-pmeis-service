<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class RJRelatedActivities
{
    public function __construct(
        private int $quarterId,
        private int $fieldOfficeId,
        private int $clientId,
        private int $offenseId,
        private string $peDate,
        private int $peVenueId,
        private int $venueId,
        private string $victims,
        private int $rjpId,
        private int $rjoId,
        private string $rjGroup,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ){}

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
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getOffenseId(): int
    {
        return $this->offenseId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPeDate(): string
    {
        return $this->peDate;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getPeVenueId(): int
    {
        return $this->peVenueId;
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
     * @return string
     */
    public function getVictims(): string
    {
        return $this->victims;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjpId(): int
    {
        return $this->rjpId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjoId(): int
    {
        return $this->rjoId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRjGroup(): string
    {
        return $this->rjGroup;
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