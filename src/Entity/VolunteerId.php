<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VolunteerIdRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VolunteerIdRepository::class)
 */
class VolunteerId
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $idNo;

    /**
     * @ORM\Column(type="integer")
     */
    private int $volunteerId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $adminName;

    public function getIdNo(): ?string
    {
        return $this->idNo;
    }

    public function setIdNo(string $idNo): self
    {
        $this->idNo = $idNo;

        return $this;
    }

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
    }

    public function setVolunteerId(int $volunteerId): self
    {
        $this->volunteerId = $volunteerId;

        return $this;
    }

    public function getAdminName(): ?string
    {
        return $this->adminName;
    }

    public function setAdminName(string $adminName): self
    {
        $this->adminName = $adminName;

        return $this;
    }
}
