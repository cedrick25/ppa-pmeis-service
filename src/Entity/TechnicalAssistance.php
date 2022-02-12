<?php

namespace App\Entity;

use App\Repository\TechnicalAssistanceRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TechnicalAssistanceRepository::class)
 */
class TechnicalAssistance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $activityName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $agencyName;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $venue;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $participantsNo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $participantsType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $personnelId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $personnelRole;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $vpaId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vpaRole;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remarks;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function setActivityName(string $activityName): self
    {
        $this->activityName = $activityName;

        return $this;
    }

    public function getAgencyName(): ?string
    {
        return $this->agencyName;
    }

    public function setAgencyName(string $agencyName): self
    {
        $this->agencyName = $agencyName;

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

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(string $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return string
     */
    public function getParticipantsNo(): string
    {
        return $this->participantsNo;
    }

    /**
     * @param string $participantsNo
     * @return TechnicalAssistance
     */
    public function setParticipantsNo(string $participantsNo): TechnicalAssistance
    {
        $this->participantsNo = $participantsNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getParticipantsType(): string
    {
        return $this->participantsType;
    }

    /**
     * @param string $participantsType
     * @return TechnicalAssistance
     */
    public function setParticipantsType(string $participantsType): TechnicalAssistance
    {
        $this->participantsType = $participantsType;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPersonnelId(): ?int
    {
        return $this->personnelId;
    }

    /**
     * @param int|null $personnelId
     * @return TechnicalAssistance
     */
    public function setPersonnelId(?int $personnelId): TechnicalAssistance
    {
        $this->personnelId = $personnelId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPersonnelRole(): ?string
    {
        return $this->personnelRole;
    }

    /**
     * @param string|null $personnelRole
     * @return TechnicalAssistance
     */
    public function setPersonnelRole(?string $personnelRole): TechnicalAssistance
    {
        $this->personnelRole = $personnelRole;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVpaId(): ?int
    {
        return $this->vpaId;
    }

    /**
     * @param int|null $vpaId
     * @return TechnicalAssistance
     */
    public function setVpaId(?int $vpaId): TechnicalAssistance
    {
        $this->vpaId = $vpaId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVpaRole(): ?string
    {
        return $this->vpaRole;
    }

    /**
     * @param string|null $vpaRole
     * @return TechnicalAssistance
     */
    public function setVpaRole(?string $vpaRole): TechnicalAssistance
    {
        $this->vpaRole = $vpaRole;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * @param string|null $remarks
     * @return TechnicalAssistance
     */
    public function setRemarks(?string $remarks): TechnicalAssistance
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return TechnicalAssistance
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): TechnicalAssistance
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeImmutable|null $updatedAt
     * @return TechnicalAssistance
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): TechnicalAssistance
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeImmutable|null $deletedAt
     * @return TechnicalAssistance
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): TechnicalAssistance
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
