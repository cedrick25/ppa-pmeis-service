<?php

namespace App\Entity;

use App\Repository\CivilStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CivilStatusRepository::class)
 */
class CivilStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $civilStatusId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getCivilStatusId(): ?int
    {
        return $this->civilStatusId;
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
