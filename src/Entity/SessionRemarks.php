<?php

namespace App\Entity;

use App\Repository\SessionRemarksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SessionRemarksRepository::class)
 */
class SessionRemarks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $sessionRemarkId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function getSessionRemarkId(): ?int
    {
        return $this->sessionRemarkId;
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
