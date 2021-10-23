<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class Phases
{
    public function __construct(
        private string $name,
        private int $sortOrder,
        private ?DateTimeImmutable $createdAt = null,
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
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}