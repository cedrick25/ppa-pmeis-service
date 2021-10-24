<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientsRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\OffenseCategory;

/**
 * @ORM\Entity(repositoryClass=ClientsRepository::class)
 */
class Clients
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $clientId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cmisId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientTypeId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $middleName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $suffix;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private string $gender;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=3, columnDefinition="enum('DO', 'NDO')")
     */
    private string $offenseCategory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $regionId;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isSeniorCitizen;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPwd;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $supervisionStart;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $supervisionEnd;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $deletedAt;

    public function getId(): ?int
    {
        return $this->clientId;
    }

    public function getCmisId(): ?int
    {
        return $this->cmisId;
    }

    public function setCmisId(int $cmisId): self
    {
        $this->cmisId = $cmisId;

        return $this;
    }

    public function getClientTypeId(): ?int
    {
        return $this->clientTypeId;
    }

    public function setClientTypeId(int $clientTypeId): self
    {
        $this->clientTypeId = $clientTypeId;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getDateOfBirth(): ?DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getOffenseCategory(): ?string
    {
        return $this->offenseCategory;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setOffenseCategory(string $offenseCategory): self
    {
        if (! OffenseCategory::isValid($offenseCategory)) {
            throw new InvalidArgumentException("Invalid Role");
        }
        $this->offenseCategory = $offenseCategory;

        return $this;
    }

    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    public function setFieldOfficeId(?int $fieldOfficeId): self
    {
        $this->fieldOfficeId = $fieldOfficeId;

        return $this;
    }

    public function getRegionId(): ?int
    {
        return $this->regionId;
    }

    public function setRegionId(?int $regionId): self
    {
        $this->regionId = $regionId;

        return $this;
    }

    public function getIsSeniorCitizen(): ?bool
    {
        return $this->isSeniorCitizen;
    }

    public function setIsSeniorCitizen(bool $isSeniorCitizen): self
    {
        $this->isSeniorCitizen = $isSeniorCitizen;

        return $this;
    }

    public function getIsPwd(): ?bool
    {
        return $this->isPwd;
    }

    public function setIsPwd(bool $isPwd): self
    {
        $this->isPwd = $isPwd;

        return $this;
    }

    public function getSupervisionStart(): ?DateTimeInterface
    {
        return $this->supervisionStart;
    }

    public function setSupervisionStart(DateTimeInterface $supervisionStart): self
    {
        $this->supervisionStart = $supervisionStart;

        return $this;
    }

    public function getSupervisionEnd(): ?DateTimeInterface
    {
        return $this->supervisionEnd;
    }

    public function setSupervisionEnd(DateTimeInterface $supervisionEnd): self
    {
        $this->supervisionEnd = $supervisionEnd;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
