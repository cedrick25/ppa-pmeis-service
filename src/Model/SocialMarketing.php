<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SocialMarketing implements \JsonSerializable
{
    public function __construct(
        private int $socialMarketingActivityId,
        private string $activityName,
        private string $date,
        private string $venue,
        private string $type,
        private array $personsInvolved,
        private array $participants,
        private string $remarks,
        private ?int $primers = 0,
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getPersonsInvolved(): array
    {
        return $this->personsInvolved;
    }

    /**
     * @return array
     */
    public function getParticipants(): array
    {
        return $this->participants;
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
     * @return int|null
     */
    public function getPrimers(): ?int
    {
        return $this->primers;
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