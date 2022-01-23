<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RJGroup;
use App\Repository\RJConductProcessesRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RJConductProcessesRepository::class)
 */
class RJConductProcesses
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $rjConductProcessId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientId;

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
    private int $offenseId;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $peDate;

    /**
     * @ORM\Column(type="integer")
     */
    private int $peVenueId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $peActivity;

    /**
     * @ORM\Column(type="integer")
     */
    private int $rjpsId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $rjoId;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('ACTIVE_SUPERVISION', 'PETITIONER')")
     */
    private string $rjGroup;

    /**
     * @ORM\Column(type="integer")
     */
    private int $plannerId;

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

    public function getRJConductProcessId(): ?int
    {
        return $this->rjConductProcessId;
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

    public function getOffenseId(): ?int
    {
        return $this->offenseId;
    }

    public function setOffenseId(int $offenseId): self
    {
        $this->offenseId = $offenseId;

        return $this;
    }

    public function getPeDate(): ?DateTimeInterface
    {
        return $this->peDate;
    }

    public function setPeDate(DateTimeInterface $peDate): self
    {
        $this->peDate = $peDate;

        return $this;
    }

    public function getPeVenueId(): ?int
    {
        return $this->peVenueId;
    }

    public function setPeVenueId(int $peVenueId): self
    {
        $this->peVenueId = $peVenueId;

        return $this;
    }

    public function getPeActivity(): ?string
    {
        return $this->peActivity;
    }

    public function setPeActivity(string $peActivity): self
    {
        $this->peActivity = $peActivity;

        return $this;
    }

    public function getRjpsId(): ?int
    {
        return $this->rjpsId;
    }

    public function setRjpsId(int $rjpsId): self
    {
        $this->rjpsId = $rjpsId;

        return $this;
    }

    public function getRjoId(): ?int
    {
        return $this->rjoId;
    }

    public function setRjoId(int $rjoId): self
    {
        $this->rjoId = $rjoId;

        return $this;
    }

    public function getRjGroup(): ?string
    {
        return $this->rjGroup;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setRjGroup(string $rjGroup): self
    {
        if (! RJGroup::isValid($rjGroup)) {
            throw new InvalidArgumentException("Invalid RJ Group");
        }
        $this->rjGroup = $rjGroup;

        return $this;
    }

    public function getPlannerId(): ?int
    {
        return $this->plannerId;
    }

    public function setPlannerId(int $plannerId): self
    {
        $this->plannerId = $plannerId;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
