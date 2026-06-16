<?php

namespace App\Entity;

use App\Repository\VolunteerSupervisionClientsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VolunteerSupervisionClientsRepository::class)
 */
class VolunteerSupervisionClients
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $volunteerSupervisionClientId;

    /**
     * @ORM\Column(type="integer")
     */
    private $volunteerSupervisionId;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    public function getVolunteerSupervisionClientId(): ?int
    {
        return $this->volunteerSupervisionClientId;
    }

    public function getVolunteerSupervisionId(): ?int
    {
        return $this->volunteerSupervisionId;
    }

    public function setVolunteerSupervisionId(int $volunteerSupervisionId): self
    {
        $this->volunteerSupervisionId = $volunteerSupervisionId;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }
}
