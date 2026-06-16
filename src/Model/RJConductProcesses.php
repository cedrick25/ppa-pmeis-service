<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class RJConductProcesses implements \JsonSerializable
{
    public function __construct(
        private array $clientIds,
        private int $quarterId,
        private int $fieldOfficeId,
        private int $offenseId,
        private int $peVenueId,
        private string $peDate,
        private string $peActivity,
        private string $rjpDate,
        private int $rjpId,
        private int $rjpVenueId,
        private int $rjpsId,
        private int $rjoId,
        private string $rjGroup,
        private int $plannerId,
        private array $personsInvolved = [],
        private int $createdBy,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ){}

    /**
     * @Assert\NotBlank
     * @return int[]
     */
    public function getClientIds(): array
    {
        return $this->clientIds;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getQuarterId(): int
    {
        return $this->quarterId;
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
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getOffenseId(): int
    {
        return $this->offenseId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPeDate(): string
    {
        return $this->peDate;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getPeVenueId(): int
    {
        return $this->peVenueId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPeActivity(): string
    {
        return $this->peActivity;
    }

    /**
     * @return string
     */
    public function getRjpDate(): string
    {
        return $this->rjpDate;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjpId(): int
    {
        return $this->rjpId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjpVenueId(): int
    {
        return $this->rjpVenueId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjpsId(): int
    {
        return $this->rjpsId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getRjoId(): int
    {
        return $this->rjoId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRjGroup(): string
    {
        return $this->rjGroup;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getPlannerId(): int
    {
        return $this->plannerId;
    }

    /**
     * @return array
     */
    public function getPersonsInvolved(): array
    {
        return $this->personsInvolved;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }
}