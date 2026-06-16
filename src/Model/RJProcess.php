<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class RJProcess
{
    public function __construct(
        private string $name,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}