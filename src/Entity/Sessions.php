<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SessionsRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\SessionPeriod;

/**
 * @ORM\Entity(repositoryClass=SessionsRepository::class)
 */
class Sessions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $sessionId;

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
    private int $phaseId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $batch;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sessionActivityId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $treatmentCategoryId;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="integer")
     */
    private int $venueId;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('AM', 'PM')")
     */
    private string $period;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    /**
     * @ORM\Column(type="integer")
     */
    private int $createdBy;

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

    public function getSessionId(): ?int
    {
        return $this->sessionId;
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

    public function getPhaseId(): ?int
    {
        return $this->phaseId;
    }

    public function setPhaseId(int $phaseId): self
    {
        $this->phaseId = $phaseId;

        return $this;
    }

    /**
     * @return string
     */
    public function getBatch(): string
    {
        return $this->batch;
    }

    /**
     * @param string $batch
     */
    public function setBatch(string $batch): void
    {
        $this->batch = $batch;
    }

    public function getSessionActivityId(): ?int
    {
        return $this->sessionActivityId;
    }

    public function setSessionActivityId(int $sessionActivityId): self
    {
        $this->sessionActivityId = $sessionActivityId;

        return $this;
    }

    public function getTreatmentCategoryId(): ?int
    {
        return $this->treatmentCategoryId;
    }

    public function setTreatmentCategoryId(int $treatmentCategoryId): self
    {
        $this->treatmentCategoryId = $treatmentCategoryId;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getVenueId(): ?int
    {
        return $this->venueId;
    }

    public function setVenueId(int $venueId): self
    {
        $this->venueId = $venueId;

        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setPeriod(string $period): self
    {
        if (! SessionPeriod::isValid($period)) {
            throw new InvalidArgumentException("Invalid Session Period");
        }
        $this->period = $period;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(int $createdBy): self
    {
        $this->createdBy = $createdBy;

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
