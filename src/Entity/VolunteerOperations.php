<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\VolunteerOperationStatus;
use App\Repository\VolunteerOperationsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VolunteerOperationsRepository::class)
 */
class VolunteerOperations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $volunteerOperationId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $volunteerId;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('APPOINTED', 'REAPPOINTED','DROPPED')")
     */
    private string $status;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $reason;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?\DateTimeImmutable $dateEndorsed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $droppedBy;

    public function getVolunteerOperationId(): ?int
    {
        return $this->volunteerOperationId;
    }

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
    }

    public function setVolunteerId(int $volunteerId): self
    {
        $this->volunteerId = $volunteerId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setStatus(string $status): self
    {
        if (! VolunteerOperationStatus::isValid($status)) {
            throw new InvalidArgumentException("Invalid Status");
        }
        $this->status = $status;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateEndorsed(): ?\DateTimeImmutable
    {
        return $this->dateEndorsed;
    }

    /**
     * @param \DateTimeImmutable|null $dateEndorsed
     * @return VolunteerOperations
     */
    public function setDateEndorsed(?\DateTimeImmutable $dateEndorsed): VolunteerOperations
    {
        $this->dateEndorsed = $dateEndorsed;
        return $this;
    }

    public function getDroppedBy(): ?int
    {
        return $this->droppedBy;
    }

    public function setDroppedBy(?int $droppedBy): self
    {
        $this->droppedBy = $droppedBy;

        return $this;
    }
}
