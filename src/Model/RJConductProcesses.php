<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RJConductProcesses
{
    public function __construct(
        private int $clientId,
        private int $quarterId,
        private int $fieldOfficeId,
        private int $offenseId,
        private int $peVenueId,
        private string $peActivity,
        private int $rjpId,
        private int $rjpVenueId,
        private int $rjpsId,
        private int $rjoId,
        private string $rjGroup,
        private int $plannerId,
        private ?DateTimeInterface $peDate = null,
        private ?DateTimeInterface $rjpDate = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ){}

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
    public function getOffenseId(): int
    {
        return $this->offenseId;
    }

    /**
     * @Assert\NotBlank
     * @return DateTimeInterface|null
     */
    public function getPeDate(): ?DateTimeInterface
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
     * @return string
     */
    public function getPeActivity(): string
    {
        return $this->peActivity;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getRjpDate(): ?DateTimeInterface
    {
        return $this->rjpDate;
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
    public function getRjpVenueId(): int
    {
        return $this->rjpVenueId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjpsId(): int
    {
        return $this->rjpsId;
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
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getPlannerId(): int
    {
        return $this->plannerId;
    }

    /**
     * @Assert\NotBlank
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