<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use Symfony\Component\Validator\Constraints as Assert;

class VolunteerOperations implements \JsonSerializable
{
    public function __construct(
        private int $volunteerId,
        private string $date,
        private string $status,
        private ?string $reason = null,
        private ?string $dateEndorsed = null,
        private ?int $droppedBy = null,
    ){}

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getVolunteerId(): int
    {
        return $this->volunteerId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return string|null
     */
    public function getDateEndorsed(): ?string
    {
        return $this->dateEndorsed;
    }

    /**
     * @return int|null
     */
    public function getDroppedBy(): ?int
    {
        return $this->droppedBy;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}