<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TechnicalAssistance implements \JsonSerializable
{
    public function __construct(
        private string $activityName,
        private string $agencyName,
        private string $date,
        private string $venue,
        private int $participantsNo,
        private int $fieldOfficeId,
        private ?string $remarks = null,
        private ?array $personsInvolved = [],
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
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * @return array|null
     */
    public function getPersonsInvolved(): ?array
    {
        return $this->personsInvolved;
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

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}