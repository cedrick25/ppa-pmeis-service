<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
class RJOutcomes
{
    public function __construct(
        private string $name,
        private string $code,
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
     * @Assert\NotBlank
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
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