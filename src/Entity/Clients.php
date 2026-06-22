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
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $cmisId;

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
    private ?string $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $suffix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $alias;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private string $gender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private string $offenseCategory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $fieldOfficeId;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $clientRemarksId;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $cmisDocketNo;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $cmisCaseClassification;

    /**
     * @ORM\Column(name="cmis_y_m", type="string", length=7, nullable=true)
     */
    private ?string $cmisYM;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private ?string $cmisSource;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $cmisSyncedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $cmisLastSeenAt;

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

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getCmisId(): ?int
    {
        return $this->cmisId;
    }

    public function setCmisId(?int $cmisId): self
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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

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

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

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

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(string $dateOfBirth): self
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

    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    public function setFieldOfficeId(int $fieldOfficeId): self
    {
        $this->fieldOfficeId = $fieldOfficeId;

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

    /**
     * @return int|null
     */
    public function getClientRemarksId(): ?int
    {
        return $this->clientRemarksId;
    }

    /**
     * @param int|null $clientRemarksId
     * @return Clients
     */
    public function setClientRemarksId(?int $clientRemarksId): Clients
    {
        $this->clientRemarksId = $clientRemarksId;
        return $this;
    }

    public function getCmisDocketNo(): ?string
    {
        return $this->cmisDocketNo;
    }

    public function setCmisDocketNo(?string $cmisDocketNo): self
    {
        $this->cmisDocketNo = $cmisDocketNo;

        return $this;
    }

    public function getCmisCaseClassification(): ?string
    {
        return $this->cmisCaseClassification;
    }

    public function setCmisCaseClassification(?string $cmisCaseClassification): self
    {
        $this->cmisCaseClassification = $cmisCaseClassification;

        return $this;
    }

    public function getCmisYM(): ?string
    {
        return $this->cmisYM;
    }

    public function setCmisYM(?string $cmisYM): self
    {
        $this->cmisYM = $cmisYM;

        return $this;
    }

    public function getCmisSource(): ?string
    {
        return $this->cmisSource;
    }

    public function setCmisSource(?string $cmisSource): self
    {
        $this->cmisSource = $cmisSource;

        return $this;
    }

    public function getCmisSyncedAt(): ?DateTimeImmutable
    {
        return $this->cmisSyncedAt;
    }

    public function setCmisSyncedAt(?DateTimeImmutable $cmisSyncedAt): self
    {
        $this->cmisSyncedAt = $cmisSyncedAt;

        return $this;
    }

    public function getCmisLastSeenAt(): ?DateTimeImmutable
    {
        return $this->cmisLastSeenAt;
    }

    public function setCmisLastSeenAt(?DateTimeImmutable $cmisLastSeenAt): self
    {
        $this->cmisLastSeenAt = $cmisLastSeenAt;

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
