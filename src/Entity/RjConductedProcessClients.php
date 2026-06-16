<?php

namespace App\Entity;

use App\Repository\RjConductedProcessClientsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RjConductedProcessClientsRepository::class)
 */
class RjConductedProcessClients
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
    private $rjConductProcessId;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRjConductProcessId(): ?int
    {
        return $this->rjConductProcessId;
    }

    public function setRjConductProcessId(int $rjConductProcessId): self
    {
        $this->rjConductProcessId = $rjConductProcessId;

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
