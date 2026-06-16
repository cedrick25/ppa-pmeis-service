<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SessionSelectedActivity implements \JsonSerializable
{
    public function __construct(
        private int $sessionId,
        private int $sessionActivityId,
        private int $treatmentCategoryId,
        private string $activityDetail = '',
        private bool $isCommunityService = false,
        private bool $isTreePlanting = false,
        private bool $isCooperativeSelfHelp = false,
        private bool $isCooperativeSelfHelpActivities = false,
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getSessionId(): int
    {
        return $this->sessionId;
    }


    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getSessionActivityId(): int
    {
        return $this->sessionActivityId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getTreatmentCategoryId(): int
    {
        return $this->treatmentCategoryId;
    }

        /**
     * @Assert\NotBlank
     * @return string
     */
    public function getActivityDetail(): string
    {
        return $this->activityDetail;
    }

   
    public function isCommunityService(): bool
    {
        return $this->isCommunityService;
    }

    public function isTreePlanting(): bool
    {
        return $this->isTreePlanting;
    }

    public function isCooperativeSelfHelp(): bool
    {
        return $this->isCooperativeSelfHelp;
    }

    public function isCooperativeSelfHelpActivities(): bool
    {
        return $this->isCooperativeSelfHelpActivities;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }

}