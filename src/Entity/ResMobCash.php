<?php

namespace App\Entity;

use App\Repository\ResMobCashRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResMobCashRepository::class)
 */
class ResMobCash
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
     * @ORM\Column(type="float")
     */
    private $amount;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function setSourceType(string $sourceType): self
    {
        $this->sourceType = $sourceType;

        return $this;
    }
}
