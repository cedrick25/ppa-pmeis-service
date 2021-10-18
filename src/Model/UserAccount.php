<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\UserType;
use DateTimeInterface;

class UserAccount
{
    public function __construct(
        private int $id,
        private string $emailAddress,
        private string $contactNumber,
        private UserType $type,
        private int $status,
        private ?Region $region,
        private ?FieldOffice $fieldOffice,
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
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return string
     */
    public function getContactNumber(): string
    {
        return $this->contactNumber;
    }

    /**
     * @return UserType
     */
    public function getType(): UserType
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @return FieldOffice|null
     */
    public function getFieldOffice(): ?FieldOffice
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