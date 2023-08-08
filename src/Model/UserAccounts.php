<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserAccounts
{
    public function __construct(
        private string $emailAddress,
        private string $contactNumber,
        private string $userType,
        private int $status,
        private ?int $region,
        private ?int $fieldOffice,
        private ?string $password = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
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
}