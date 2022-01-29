<?php

namespace App\Entity;

use App\Repository\PaymentModesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaymentModesRepository::class)
 */
class PaymentModes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $paymentModeId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $deletedAt;

    public function getPaymentModeId(): ?int
    {
        return $this->paymentModeId;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
