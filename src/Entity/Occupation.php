<?php

namespace App\Entity;

use App\Repository\OccupationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OccupationRepository::class)
 */
class Occupation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $occupationId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getOccupationIdId(): ?int
    {
        return $this->occupationId;
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
