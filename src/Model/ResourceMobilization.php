<?php

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;
class ResourceMobilization
{
    public function __construct(
        private string $category,
        private string $activityName,
        private string $date,
        private string $venue,
        private float $amount,
        private string $cashSourceName,
        private string $cashSourceType,
        private int $materialsId,
        private int $materialsQty,
        private string $materialSourceName,
        private string $materialSourceType,
        private string $technicalAssistanceParticulars,
        private float $technicalAssistanceAmount,
        private string $technicalAssistanceName,
        private string $technicalAssistanceType,
        private string $resourcesSecuredBy,
        private int $fieldOfficeId,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
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
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCashSourceName(): string
    {
        return $this->cashSourceName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCashSourceType(): string
    {
        return $this->cashSourceType;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getMaterialsId(): int
    {
        return $this->materialsId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getMaterialsQty(): int
    {
        return $this->materialsQty;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getMaterialSourceName(): string
    {
        return $this->materialSourceName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getMaterialSourceType(): string
    {
        return $this->materialSourceType;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getTechnicalAssistanceParticulars(): string
    {
        return $this->technicalAssistanceParticulars;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getTechnicalAssistanceAmount(): float
    {
        return $this->technicalAssistanceAmount;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getTechnicalAssistanceName(): string
    {
        return $this->technicalAssistanceName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getTechnicalAssistanceType(): string
    {
        return $this->technicalAssistanceType;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getResourcesSecuredBy(): string
    {
        return $this->resourcesSecuredBy;
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