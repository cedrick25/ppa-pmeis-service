<?php

namespace App\Entity;

use App\Repository\ResMobTechnicalAssistanceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResMobTechnicalAssistanceRepository::class)
 */
class ResMobTechnicalAssistance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $resMobId;

    /**
     * @ORM\Column(type="integer")
     */
    private $particularQuantity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $particularType;

    /**
     * @ORM\Column(type="integer")
     */
    private $estimatedAmount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sourceName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sourceType;

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

    public function getParticularQuantity(): ?int
    {
        return $this->particularQuantity;
    }

    public function setParticularQuantity(int $particularQuantity): self
    {
        $this->particularQuantity = $particularQuantity;

        return $this;
    }

    public function getParticularType(): ?string
    {
        return $this->particularType;
    }

    public function setParticularType(string $particularType): self
    {
        $this->particularType = $particularType;

        return $this;
    }

    public function getEstimatedAmount(): ?int
    {
        return $this->estimatedAmount;
    }

    public function setEstimatedAmount(int $estimatedAmount): self
    {
        $this->estimatedAmount = $estimatedAmount;

        return $this;
    }

    public function getSourceName(): ?string
    {
        return $this->sourceName;
    }

    public function setSourceName(string $sourceName): self
    {
        $this->sourceName = $sourceName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @param mixed $sourceType
     */
    public function setSourceType($sourceType): void
    {
        $this->sourceType = $sourceType;
    }
}
