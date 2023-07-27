<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SessionActivitiesRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SessionActivitiesRepository::class)
 */
class SessionActivities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $sessionActivityId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $treatmentCategoryId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $phaseId;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $deletedAt;

    public function getSessionActivityId(): ?int
    {
        return $this->sessionActivityId;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPhaseId(): ?int
    {
        return $this->phaseId;
    }

    /**
     * @param int|null $phaseId
     */
    public function setPhaseId(?int $phaseId): void
    {
        $this->phaseId = $phaseId;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): self
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
