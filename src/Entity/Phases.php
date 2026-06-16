<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PhasesRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Phases as PhasesEnum;

/**
 * @ORM\Entity(repositoryClass=PhasesRepository::class)
 */
class Phases
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $phaseId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sortOrder;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    public function getPhaseId(): ?int
    {
        return $this->phaseId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setName(string $name): self
    {
        if (! PhasesEnum::isValid($name)) {
            throw new InvalidArgumentException("Invalid Phase");
        }
        $this->name = $name;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
