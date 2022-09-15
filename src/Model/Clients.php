<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Clients implements \JsonSerializable
{
    public function __construct(
        private int $cmisId,
        private int $clientTypeId,
        private string $firstName,
        private string $lastName,
        private string $gender,
        private string $dateOfBirth,
        private string $offenseCategory,
        private bool $isSeniorCitizen,
        private bool $isPwd,
        private string $supervisionStart,
        private string $supervisionEnd,
        private int $fieldOfficeId,
        private ?string $middleName = null,
        private ?string $suffix = null,
        private ?int $clientRemarksId = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getCmisId(): int
    {
        return $this->cmisId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getClientTypeId(): int
    {
        return $this->clientTypeId;
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
     * @Assert\NotBlank
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string
     */
    public function getOffenseCategory(): string
    {
        return $this->offenseCategory;
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
     * @Assert\NotBlank
     * @return string
     */
    public function getSupervisionStart(): string
    {
        return $this->supervisionStart;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getSupervisionEnd(): string
    {
        return $this->supervisionEnd;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @return int|null
     */
    public function getClientRemarksId(): ?int
    {
        return $this->clientRemarksId;
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

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}