<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Sessions
{
    public function __construct(
        private int $remarksId,
        private int $fieldOfficeId,
        private int $phaseId,
        private string $batch,
        private int $sessionActivityId,
        private int $treatmentCategoryId,
        private string $date,
        private int $venueId,
        private string $period,
        private int $fsg,
        private string $liLo,
        private int $createdBy,
        private ?int $treesPlanted = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null,
        private ?array $clientSession = null,
        private ?array $facilitators = null,
        private ?array $absentees = null,
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRemarksId(): int
    {
        return $this->remarksId;
    }

    /**
     * @return int|null
     */
    public function getTreesPlanted(): ?int
    {
        return $this->treesPlanted;
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
    public function getPhaseId(): int
    {
        return $this->phaseId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getBatch(): string
    {
        return $this->batch;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getSessionActivityId(): int
    {
        return $this->sessionActivityId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getTreatmentCategoryId(): int
    {
        return $this->treatmentCategoryId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
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
     * @Assert\Length(2)
     * @return string
     */
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getFsg(): int
    {
        return $this->fsg;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(2)
     * @return string
     */
    public function getLiLo(): string
    {
        return $this->liLo;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @return array<string, int[]>|null
     */
    public function getClientSession(): ?array
    {
        return $this->clientSession;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFacilitators(): ?array
    {
        return $this->facilitators;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAbsentees(): ?array
    {
        return $this->absentees;
    }
}