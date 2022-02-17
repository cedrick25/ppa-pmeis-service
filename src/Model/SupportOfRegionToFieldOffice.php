<?php

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SupportOfRegionToFieldOffice
{
    public function __construct(
        private string $category,
        private string $subCategory,
        private string $date,
        private int $fieldOfficeId,
        private string $particulars,
        private float $amount,
        private float $attributableCost,
        private float $totalAmount,
        private string $remarks,
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
    public function getSubCategory(): string
    {
        return $this->subCategory;
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
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getParticulars(): string
    {
        return $this->particulars;
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
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getAttributableCost(): float
    {
        return $this->attributableCost;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
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