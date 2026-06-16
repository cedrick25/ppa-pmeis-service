<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Sessions implements \JsonSerializable
{
    public function __construct(
        private int $fieldOfficeId,
        private int $phaseId,
        private string $batch,
        private int $sessionActivityId,
        private int $treatmentCategoryId,
        private string $date,
        private int $venueId,
        private string $period,
        private string $liLo,
        private int $createdBy,
        private ?int $treesPlanted = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null,
        private ?array $activities = null,
        private ?array $attendees = null,
        private ?array $facilitators = null,
        private ?array $absentees = null,
        private ?int $fsgNumber = null,
        private string $activityDetail = '',
        private bool $isCommunityService = false,
        private bool $isTreePlanting = false,
        private bool $isCooperativeSelfHelp = false,
        private bool $isCooperativeSelfHelpActivities = false,
        
    ){}

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
     * @Assert\Length(2)
     * @return string
     */
    public function getLiLo(): string
    {
        return $this->liLo;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getActivityDetail(): string
    {
        return $this->activityDetail;
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
    public function getAttendees(): ?array
    {
        return $this->attendees;
    }

    /**
     * @return array<string, int[]>|null
     */
    public function getActivities(): ?array
    {
        return $this->activities;
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

    /**
     * @return int|null
     */
    public function getFsgNumber(): ?int
    {
        return $this->fsgNumber;
    }
    
    public function isCommunityService(): bool
    {
        return $this->isCommunityService;
    }

    public function isTreePlanting(): bool
    {
        return $this->isTreePlanting;
    }

    public function isCooperativeSelfHelp(): bool
    {
        return $this->isCooperativeSelfHelp;
    }

    public function isCooperativeSelfHelpActivities(): bool
    {
        return $this->isCooperativeSelfHelpActivities;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}