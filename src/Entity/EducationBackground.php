<?php

namespace App\Entity;

use App\Repository\EducationBackgroundRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EducationBackgroundRepository::class)
 */
class EducationBackground
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $educationBackgroundId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getEducationBackgroundId(): ?int
    {
        return $this->educationBackgroundId;
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
