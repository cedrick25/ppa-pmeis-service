<?php

namespace App\Entity;

use App\Repository\ResMobSecuredByRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResMobSecuredByRepository::class)
 */
class ResMobSecuredBy
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $resMobId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $securedById;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $othersName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResMobId(): ?int
    {
        return $this->resMobId;
    }

    public function setResMobId(int $resMobId): self
    {
        $this->resMobId = $resMobId;

        return $this;
    }

    public function getSecuredById(): ?int
    {
        return $this->securedById;
    }

    public function setSecuredById(int $securedById): self
    {
        $this->securedById = $securedById;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOthersName(): ?string
    {
        return $this->othersName;
    }

    /**
     * @param string|null $othersName
     */
    public function setOthersName(?string $othersName): void
    {
        $this->othersName = $othersName;
    }
}
