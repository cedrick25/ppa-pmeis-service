<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RJGroup;
use App\Repository\RJRelatedActivitiesRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RJRelatedActivitiesRepository::class)
 */
class RJRelatedActivities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $rjRelatedActivityId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quarterId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $offenseId;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $venueDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $victims;

    /**
     * @ORM\Column(type="integer")
     */
    private int $rjpId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $venueId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $rjoId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $rjGroup;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $deletedAt;

    public function getRjRelatedActivityId(): ?int
    {
        return $this->rjRelatedActivityId;
    }

    public function getQuarterId(): ?int
    {
        return $this->quarterId;
    }

    public function setQuarterId(int $quarterId): self
    {
        $this->quarterId = $quarterId;

        return $this;
    }

    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    public function setFieldOfficeId(int $fieldOfficeId): self
    {
        $this->fieldOfficeId = $fieldOfficeId;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getOffenseId(): ?int
    {
        return $this->offenseId;
    }

    public function setOffenseId(int $offenseId): self
    {
        $this->offenseId = $offenseId;

        return $this;
    }

    public function getVenueDate(): ?DateTimeInterface
    {
        return $this->venueDate;
    }

    public function setVenueDate(DateTimeInterface $venueDate): self
    {
        $this->venueDate = $venueDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getVictims(): string
    {
        return $this->victims;
    }

    /**
     * @param string $victims
     * @return RJRelatedActivities
     */
    public function setVictims(string $victims): RJRelatedActivities
    {
        $this->victims = $victims;
        return $this;
    }

    /**
     * @return int
     */
    public function getRjpId(): int
    {
        return $this->rjpId;
    }

    /**
     * @param int $rjpId
     * @return RJRelatedActivities
     */
    public function setRjpId(int $rjpId): RJRelatedActivities
    {
        $this->rjpId = $rjpId;
        return $this;
    }

    /**
     * @return int
     */
    public function getVenueId(): int
    {
        return $this->venueId;
    }

    /**
     * @param int $venueId
     * @return RJRelatedActivities
     */
    public function setVenueId(int $venueId): RJRelatedActivities
    {
        $this->venueId = $venueId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRjoId(): int
    {
        return $this->rjoId;
    }

    /**
     * @param int $rjoId
     * @return RJRelatedActivities
     */
    public function setRjoId(int $rjoId): RJRelatedActivities
    {
        $this->rjoId = $rjoId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRjGroup(): string
    {
        return $this->rjGroup;
    }

    /**
     * @param string $rjGroup
     * @return RJRelatedActivities
     * @throws InvalidArgumentException
     */
    public function setRjGroup(string $rjGroup): RJRelatedActivities
    {
        if (! RJGroup::isValid($rjGroup)) {
            throw new InvalidArgumentException("Invalid RJ Group");
        }
        $this->rjGroup = $rjGroup;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
