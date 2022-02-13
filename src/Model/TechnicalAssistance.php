<?php

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TechnicalAssistance
{
    public function __construct(
        private string $activityName,
        private string $agencyName,
        private string $date,
        private string $venue,
        private int $participantsNo,
        private int $fieldOfficeId,
        private string $participantsType,
        private ?int $personnelId = null,
        private ?string $personnelRole = null,
        private ?int $vpaId = null,
        private ?string $vpaRole = null,
        private ?string $remarks = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null,
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getActivityName(): string
    {
        return $this->activityName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getAgencyName(): string
    {
        return $this->agencyName;
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
     * @return string
     */
    public function getVenue(): string
    {
        return $this->venue;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getParticipantsNo(): int
    {
        return $this->participantsNo;
    }

    /**
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getParticipantsType(): string
    {
        return $this->participantsType;
    }

    /**
     * @return int|null
     */
    public function getPersonnelId(): ?int
    {
        return $this->personnelId;
    }

    /**
     * @return string|null
     */
    public function getPersonnelRole(): ?string
    {
        return $this->personnelRole;
    }

    /**
     * @return int|null
     */
    public function getVpaId(): ?int
    {
        return $this->vpaId;
    }

    /**
     * @return string|null
     */
    public function getVpaRole(): ?string
    {
        return $this->vpaRole;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
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
}