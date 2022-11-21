<?php

namespace App\Entity;

use App\Repository\SpecialAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SpecialAssignmentRepository::class)
 */
class SpecialAssignment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $specialAssignmentId;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $categoryType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $decsription;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $activity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $venue;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remarks;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $deletedAt;

    public function getSpecialAssignmentId(): ?int
    {
        return $this->specialAssignmentId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeInterface $startDate
     */
    public function setStartDate(\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeInterface $endDate
     */
    public function setEndDate(\DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getCategoryType(): ?string
    {
        return $this->categoryType;
    }

    public function setCategoryType(string $categoryType): self
    {
        $this->categoryType = $categoryType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @param mixed $subType
     */
    public function setSubType($subType): void
    {
        $this->subType = $subType;
    }

    public function getDecsription(): ?string
    {
        return $this->decsription;
    }

    public function setDecsription(string $decsription): self
    {
        $this->decsription = $decsription;

        return $this;
    }

    public function getActivity(): ?string
    {
        return $this->activity;
    }

    public function setActivity(string $activity): self
    {
        $this->activity = $activity;

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

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

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
