<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserDetails
{
    public function __construct(
        private int $userAccountId,
        private string $firstName,
        private ?string $middleName = null,
        private string $lastName,
        private ?string $suffix = null,
        private string $gender,
        private string $dateOfBirth,
        private bool $isSeniorCitizen = false,
        private bool $isPwd = false,
        private ?string $updatedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getUserAccountId(): int
    {
        return $this->userAccountId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(1)
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @Assert\NotBlank
     * @Assert\DateTime()
     * @return string
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return bool
     */
    public function isSeniorCitizen(): bool
    {
        return $this->isSeniorCitizen;
    }

    /**
     * @return bool
     */
    public function isPwd(): bool
    {
        return $this->isPwd;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}