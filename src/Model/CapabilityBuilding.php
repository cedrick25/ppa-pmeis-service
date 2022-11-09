<?php

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;
class CapabilityBuilding
{

    public function __construct(
        private string $type,
        private string $subtype,
        private string $title,
        private string $date,
        private int $noOfParticipants,
        private int $fieldOfficeId,
        private bool $isPwd = false,
        private bool $isSeniorCitizen = false,
        private ?string $notManagerialSupervisory = null,
        private ?string $notTechnical = null,
        private ?string $notFoundation = null,
        private int $noOfTrainingHours = 0,
        private ?string $tcInHouse = null,
        private ?string $tcOutHouse = null,
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
     * @return string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
    public function getNoOfParticipants(): int
    {
        return $this->noOfParticipants;
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
     * @return bool
     */
    public function isPwd(): bool
    {
        return $this->isPwd;
    }

    /**
     * @return bool
     */
    public function isSeniorCitizen(): bool
    {
        return $this->isSeniorCitizen;
    }

    /**
     * @return string|null
     */
    public function getNotManagerialSupervisory(): ?string
    {
        return $this->notManagerialSupervisory;
    }

    /**
     * @return string|null
     */
    public function getNotTechnical(): ?string
    {
        return $this->notTechnical;
    }

    /**
     * @return string|null
     */
    public function getNotFoundation(): ?string
    {
        return $this->notFoundation;
    }

    /**
     * @return int
     */
    public function getNoOfTrainingHours(): int
    {
        return $this->noOfTrainingHours;
    }

    /**
     * @return string|null
     */
    public function getTcInHouse(): ?string
    {
        return $this->tcInHouse;
    }

    /**
     * @return string|null
     */
    public function getTcOutHouse(): ?string
    {
        return $this->tcOutHouse;
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