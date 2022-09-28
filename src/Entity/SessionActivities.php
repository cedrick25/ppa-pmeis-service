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
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $phaseId;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isCommunityService = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isTreePlanting = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isCooperativeSelfHelp = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isCooperativeSelfHelpActivities = false;

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

    /**
     * @return bool
     */
    public function isCommunityService(): bool
    {
        return $this->isCommunityService;
    }

    /**
     * @param bool $isCommunityService
     */
    public function setIsCommunityService(bool $isCommunityService): void
    {
        $this->isCommunityService = $isCommunityService;
    }

    /**
     * @return bool
     */
    public function isTreePlanting(): bool
    {
        return $this->isTreePlanting;
    }

    /**
     * @param bool $isTreePlanting
     */
    public function setIsTreePlanting(bool $isTreePlanting): void
    {
        $this->isTreePlanting = $isTreePlanting;
    }

    /**
     * @return bool
     */
    public function isCooperativeSelfHelp(): bool
    {
        return $this->isCooperativeSelfHelp;
    }

    /**
     * @param bool $isCooperativeSelfHelp
     */
    public function setIsCooperativeSelfHelp(bool $isCooperativeSelfHelp): void
    {
        $this->isCooperativeSelfHelp = $isCooperativeSelfHelp;
    }

    /**
     * @return bool
     */
    public function isCooperativeSelfHelpActivities(): bool
    {
        return $this->isCooperativeSelfHelpActivities;
    }

    /**
     * @param bool $isCooperativeSelfHelpActivities
     */
    public function setIsCooperativeSelfHelpActivities(bool $isCooperativeSelfHelpActivities): void
    {
        $this->isCooperativeSelfHelpActivities = $isCooperativeSelfHelpActivities;
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
