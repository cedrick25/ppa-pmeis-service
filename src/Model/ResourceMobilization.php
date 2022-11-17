<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResourceMobilization implements \JsonSerializable
{
    public function __construct(
        private string $category,
        private string $activityName,
        private string $date,
        private string $venue,
        private array $cash,
        private array $materials,
        private array $technicalAssistance,
        private array $securedBy,
        private int $fieldOfficeId,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ) {}

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
     * @return array
     */
    public function getCash(): array
    {
        return $this->cash;
    }

    /**
     * @return array
     */
    public function getMaterials(): array
    {
        return $this->materials;
    }

    /**
     * @return array
     */
    public function getTechnicalAssistance(): array
    {
        return $this->technicalAssistance;
    }

    /**
     * @return array
     */
    public function getSecuredBy(): array
    {
        return $this->securedBy;
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

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}