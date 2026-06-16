<?php

namespace App\Entity;

use App\Repository\CapabilityBuildingParticipantsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CapabilityBuildingParticipantsRepository::class)
 */
class CapabilityBuildingParticipants
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $capabilityBuildingId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $personnelName;

    /**
     * @ORM\Column(type="integer")
     */
    private int $personnelId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapabilityBuildingId(): ?int
    {
        return $this->capabilityBuildingId;
    }

    public function setCapabilityBuildingId(int $capabilityBuildingId): self
    {
        $this->capabilityBuildingId = $capabilityBuildingId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonnelName()
    {
        return $this->personnelName;
    }

    /**
     * @param mixed $personnelName
     */
    public function setPersonnelName($personnelName): void
    {
        $this->personnelName = $personnelName;
    }

    public function getPersonnelId(): ?int
    {
        return $this->personnelId;
    }

    public function setPersonnelId(int $personnelId): self
    {
        $this->personnelId = $personnelId;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }
}
