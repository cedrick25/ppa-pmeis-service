<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use Symfony\Component\Validator\Constraints as Assert;

class VolunteerId implements \JsonSerializable
{
    public function __construct(
        private string $idNo,
        private int $volunteerId,
        private string $adminName,
        private string $image,
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

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}