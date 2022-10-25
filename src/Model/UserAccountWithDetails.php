<?php

declare(strict_types=1);

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;


class UserAccountWithDetails implements \JsonSerializable
{
    public function __construct(
        private string $emailAddress,
        private string $contactNumber,
        private string $password,
        private string $userType,
        private int $status,
        private ?int $region,
        private ?int $fieldOffice,
        private int $userAccountId,
        private string $firstName,
        private string $lastName,
        private string $gender,
        private string $dateOfBirth,
        private int $positionId,
        private bool $isSeniorCitizen = false,
        private bool $isPwd = false,
        private ?string $middleName = null,
        private ?string $suffix = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null,
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(11)
     * @return string
     */
    public function getContactNumber(): string
    {
        return $this->contactNumber;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=8)
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5)
     * @return string
     */
    public function getUserType(): string
    {
        return $this->userType;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(1)
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int|null
     */
    public function getRegion(): ?int
    {
        return $this->region;
    }

    /**
     * @return int|null
     */
    public function getFieldOffice(): ?int
    {
        return $this->fieldOffice;
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
     * @return string
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getPositionId(): int
    {
        return $this->positionId;
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

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}