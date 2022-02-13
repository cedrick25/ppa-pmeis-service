<?php

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SocialMarketing
{
    public function __construct(
        private int $socialMarketingActivityId,
        private string $activityName,
        private string $date,
        private string $venue,
        private string $participants,
        private string $type,
        private string $remarks,
        private ?int $personnelId = null,
        private ?string $personnelRole = null,
        private ?string $vpaId = null,
        private ?string $vpaRole = null,
        private ?string $fieldOfficeId = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null,
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getSocialMarketingActivityId(): int
    {
        return $this->socialMarketingActivityId;
    }

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
     * @return string
     */
    public function getParticipants(): string
    {
        return $this->participants;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @return string|null
     */
    public function getVpaId(): ?string
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
    public function getFieldOfficeId(): ?string
    {
        return $this->fieldOfficeId;
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