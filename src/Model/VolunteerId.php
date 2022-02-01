<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class VolunteerId
{
    public function __construct(
        private string $idNo,
        private int $volunteerId,
        private string $adminName,
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getIdNo(): string
    {
        return $this->idNo;
    }

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
    public function getAdminName(): string
    {
        return $this->adminName;
    }
}