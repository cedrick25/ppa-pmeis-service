<?php

namespace App\Entity;

use App\Repository\ClientRemarksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientRemarksRepository::class)
 */
class ClientRemarks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $clientRemarksId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getClientRemarksId(): ?int
    {
        return $this->clientRemarksId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
