<?php

namespace App\Entity;

use App\Repository\ActiveSupervisionRemarksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActiveSupervisionRemarksRepository::class)
 */
class ActiveSupervisionRemarks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $activeSupervisionRemarkId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getActiveSupervisionRemarkId(): ?int
    {
        return $this->activeSupervisionRemarkId;
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
