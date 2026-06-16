<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeInterface;

class Regions
{
    public function __construct(
        private int $id,
        private string $name,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ){}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}