<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class VolunteerOperations
{
    public function __construct(
        private int $volunteerId,
        private string $date,
        private string $status,
        private int $droppedBy,
        private ?string $reason = null,
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
     * @Assert\NotBlank
     * @return int
     */
    public function getDroppedBy(): int
    {
        return $this->droppedBy;
    }
}