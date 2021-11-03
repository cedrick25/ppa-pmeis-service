<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class Volunteer
{
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string $gender,
        private string $dateOfBirth,
        private ?string $middleName = "",
        private ?string $suffix = null,
        private ?int $fieldOfficeId = null,
        private ?bool $isSeniorCitizen = false,
        private ?bool $isPwd = false,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
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
     * @return string
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @Assert\GreaterThan(0)
     * @return int|null
     */
    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @return bool|null
     */
    public function getIsSeniorCitizen(): ?bool
    {
        return $this->isSeniorCitizen;
    }

    /**
     * @return bool|null
     */
    public function getIsPwd(): ?bool
    {
        return $this->isPwd;
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