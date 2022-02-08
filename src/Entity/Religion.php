<?php

namespace App\Entity;

use App\Repository\ReligionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReligionRepository::class)
 */
class Religion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $religionId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getReligionId(): ?int
    {
        return $this->religionId;
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
