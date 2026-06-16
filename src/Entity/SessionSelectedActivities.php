<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\LiLo;
use App\Repository\SessionsRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\SessionPeriod;

/**
 * @ORM\Entity(repositoryClass=SessionSelectedActivityRepository::class)
 */
class SessionSelectedActivities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $sessionSelectedActivityId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sessionActivityId;

      /**
     * @ORM\Column(type="integer")
     */
    private int $sessionId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $treatmentCategoryId;

     /**
     * @ORM\Column(type="string", length=255)
     */
    private string $activityDetail;
    
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



    public function getSessionSelectedActivityId(): int
    {
        return $this->sessionSelectedActivityId;
    }

    public function getSessionId():  int
    {
        return $this->sessionId;
    }

    public function setSessionId(int $sessionId) : self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSessionActivityId(): int
    {
        return $this->sessionActivityId;
    }

    public function setSessionActivityId(int $sessionActivityId): self
    {
        $this->sessionActivityId = $sessionActivityId;

        return $this;
    }

    public function getTreatmentCategoryId(): int
    {
        return $this->treatmentCategoryId;
    }

    public function setTreatmentCategoryId(int $treatmentCategoryId): self
    {
        $this->treatmentCategoryId = $treatmentCategoryId;

        return $this;
    }

       public function getActivityDetail(): string
    {
        return $this->activityDetail;
    }

    public function setActivityDetail(string $activityDetail): self
    {
        $this->activityDetail = $activityDetail;

        return $this;
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

}
