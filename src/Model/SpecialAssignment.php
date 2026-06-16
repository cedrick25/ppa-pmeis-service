<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;
class SpecialAssignment implements \JsonSerializable
{
    public function __construct(
        private string $startDate,
        private string $endDate,
        private string $categoryType,
        private string $decsription,
        private string $activity,
        private string $venue,
        private array $personnelInvolved,
        private ?string $remarks,
        private int $fieldOfficeId,
        private ?string $subType = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ) {}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCategoryType(): string
    {
        return $this->categoryType;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDecsription(): string
    {
        return $this->decsription;
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
    public function getVenue(): string
    {
        return $this->venue;
    }

    /**
     * @return array
     */
    public function getPersonnelInvolved(): array
    {
        return $this->personnelInvolved;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
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
     * @return string|null
     */
    public function getSubType(): ?string
    {
        return $this->subType;
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