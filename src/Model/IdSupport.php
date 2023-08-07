<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class IdSupport implements \JsonSerializable
{
    public function __construct(
        private string $type,
        private int $vpaPersonnelId,
        private string $program,
        private int $fieldOfficeId,
        private int $assistedFieldOfficeId,
        private string $activity,
        private string $date,
        private string $venue,
        private string $assistanceRendered,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getVpaPersonnelId(): int
    {
        return $this->vpaPersonnelId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getProgram(): string
    {
        return $this->program;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getAssistedFieldOfficeId(): int
    {
        return $this->assistedFieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getActivity(): string
    {
        return $this->activity;
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
    public function getAssistanceRendered(): string
    {
        return $this->assistanceRendered;
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