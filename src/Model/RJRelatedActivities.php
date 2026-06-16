<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class RJRelatedActivities implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $personsInvolved
     */
    public function __construct(
        private int $quarterId,
        private int $fieldOfficeId,
        private int $clientId,
        private int $offenseId,
        private string $venueDate,
        private int $venueId,
        private string $victims,
        private int $rjpId,
        private int $rjoId,
        private string $rjGroup,
        private ?array $personsInvolved = [],
        private int $createdBy,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ){}

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
    public function getClientId(): int
    {
        return $this->clientId;
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
    public function getVenueDate(): string
    {
        return $this->venueDate;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getVenueId(): int
    {
        return $this->venueId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getVictims(): string
    {
        return $this->victims;
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
     * @return array|null
     */
    public function getPersonsInvolved(): ?array
    {
        return $this->personsInvolved;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
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
}