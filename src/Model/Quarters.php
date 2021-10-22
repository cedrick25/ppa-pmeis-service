<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class Quarters
{
    public function __construct(
        private string $name,
        private string $year,
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
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

}