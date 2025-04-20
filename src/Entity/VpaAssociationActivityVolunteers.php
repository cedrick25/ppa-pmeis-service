<?php

namespace App\Entity;

use App\Repository\VpaAssociationActivityVolunteersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VpaAssociationActivityVolunteersRepository::class)
 */
class VpaAssociationActivityVolunteers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $vpaActivityVounteerId;

    /**
     * @ORM\Column(type="integer")
     */
    private $volunteerId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVpaActivityVounteerId(): ?int
    {
        return $this->vpaActivityVounteerId;
    }

    public function setVpaActivityVounteerId(int $vpaActivityVounteerId): self
    {
        $this->vpaActivityVounteerId = $vpaActivityVounteerId;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
