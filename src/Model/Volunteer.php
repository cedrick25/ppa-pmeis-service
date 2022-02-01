<?php

declare(strict_types=1);

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
        private string $dateRecruited,
        private string $recruitingOfficer,
        private int $age,
        private string $birthPlace,
        private string $civilStatus,
        private string $religion,
        private string $presentAddress,
        private float $height,
        private float $weight,
        private string $bloodType,
        private string $occupation,
        private string $educationAttainment,
        private string $contactNumber,
        private string $emailAddress,
        private string $specialSkill,
        private string $emergencyName,
        private string $emergencyNumber,
        private string $image,
        private string $applicantSignature,
        private string $dateAccomplished,
        private string $officerSignature,
        private string $dateSigned,
        private ?string $middleName = null,
        private ?string $suffix = null,
        private ?int $fieldOfficeId = null,
        private ?string $domesticPartner = null,
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
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int|null
     */
    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDateRecruited(): string
    {
        return $this->dateRecruited;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRecruitingOfficer(): string
    {
        return $this->recruitingOfficer;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getBirthPlace(): string
    {
        return $this->birthPlace;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCivilStatus(): string
    {
        return $this->civilStatus;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getReligion(): string
    {
        return $this->religion;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPresentAddress(): string
    {
        return $this->presentAddress;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getBloodType(): string
    {
        return $this->bloodType;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getOccupation(): string
    {
        return $this->occupation;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getEducationAttainment(): string
    {
        return $this->educationAttainment;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getContactNumber(): string
    {
        return $this->contactNumber;
    }

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
     * @return string|null
     */
    public function getDomesticPartner(): ?string
    {
        return $this->domesticPartner;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getSpecialSkill(): string
    {
        return $this->specialSkill;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getEmergencyName(): string
    {
        return $this->emergencyName;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getEmergencyNumber(): string
    {
        return $this->emergencyNumber;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getApplicantSignature(): string
    {
        return $this->applicantSignature;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDateAccomplished(): string
    {
        return $this->dateAccomplished;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getOfficerSignature(): string
    {
        return $this->officerSignature;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDateSigned(): string
    {
        return $this->dateSigned;
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