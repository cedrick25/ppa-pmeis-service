<?php

namespace App\Entity;

use App\Repository\SystemCodeSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=SystemCodeSettingsRepository::class)
 */
class SystemCodeSettings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     */
    private string $systemCodeId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $value;

    /**
     * @ORM\Column(type="integer")
     */
    private int $createdBy;

    public function getSystemCodeId(): string
    {
        return $this->systemCodeId;
    }

    /**
     * @param string $systemCodeId
     */
    public function setSystemCodeId(string $systemCodeId): void
    {
        $this->systemCodeId = $systemCodeId;
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy(int $createdBy): void
    {
        $this->createdBy = $createdBy;
    }
}
