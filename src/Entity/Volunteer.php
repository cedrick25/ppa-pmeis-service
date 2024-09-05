<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VolunteerRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VolunteerRepository::class)
 */
class Volunteer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $volunteerId;

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
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private ?string $suffix;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private string $gender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $dateOfBirth;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isSeniorCitizen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isPwd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fieldOfficeId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isTcTrained;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $dateRecruited;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $recruitingOfficer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $age;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $birthPlace;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $civilStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $religion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $presentAddress;

    /**
     * @ORM\Column(type="float")
     */
    private float $height;

    /**
     * @ORM\Column(type="float")
     */
    private float $weight;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $bloodType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $occupation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $educationAttainment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $contactNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $emailAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $domesticPartner;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $specialSkill;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $emergencyName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $emergencyNumber;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $dateAccomplished;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $dateSigned;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $dateAppointed;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $vpaStatus;
  
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $idNumber;

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

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
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

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(string $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getIsSeniorCitizen(): ?bool
    {
        return $this->isSeniorCitizen;
    }

    public function setIsSeniorCitizen(?bool $isSeniorCitizen): self
    {
        $this->isSeniorCitizen = $isSeniorCitizen;

        return $this;
    }

    public function getIsPwd(): ?bool
    {
        return $this->isPwd;
    }

    public function setIsPwd(?bool $isPwd): self
    {
        $this->isPwd = $isPwd;

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

    /**
     * @return bool|null
     */
    public function getIsTcTrained(): ?bool
    {
        return $this->isTcTrained;
    }

    /**
     * @param bool|null $isTcTrained
     */
    public function setIsTcTrained(?bool $isTcTrained): void
    {
        $this->isTcTrained = $isTcTrained;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateRecruited(): DateTimeImmutable
    {
        return $this->dateRecruited;
    }

    /**
     * @param DateTimeImmutable $dateRecruited
     * @return Volunteer
     */
    public function setDateRecruited(DateTimeImmutable $dateRecruited): Volunteer
    {
        $this->dateRecruited = $dateRecruited;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecruitingOfficer(): string
    {
        return $this->recruitingOfficer;
    }

    /**
     * @param string $recruitingOfficer
     * @return Volunteer
     */
    public function setRecruitingOfficer(string $recruitingOfficer): Volunteer
    {
        $this->recruitingOfficer = $recruitingOfficer;
        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     * @return Volunteer
     */
    public function setAge(int $age): Volunteer
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthPlace(): string
    {
        return $this->birthPlace;
    }

    /**
     * @param string $birthPlace
     * @return Volunteer
     */
    public function setBirthPlace(string $birthPlace): Volunteer
    {
        $this->birthPlace = $birthPlace;
        return $this;
    }

    /**
     * @return int
     */
    public function getCivilStatus(): int
    {
        return $this->civilStatus;
    }

    /**
     * @param int $civilStatus
     * @return Volunteer
     */
    public function setCivilStatus(int $civilStatus): Volunteer
    {
        $this->civilStatus = $civilStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getReligion(): int
    {
        return $this->religion;
    }

    /**
     * @param int $religion
     * @return Volunteer
     */
    public function setReligion(int $religion): Volunteer
    {
        $this->religion = $religion;
        return $this;
    }

    /**
     * @return string
     */
    public function getPresentAddress(): string
    {
        return $this->presentAddress;
    }

    /**
     * @param string $presentAddress
     * @return Volunteer
     */
    public function setPresentAddress(string $presentAddress): Volunteer
    {
        $this->presentAddress = $presentAddress;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return Volunteer
     */
    public function setHeight(float $height): Volunteer
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return Volunteer
     */
    public function setWeight(float $weight): Volunteer
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getBloodType(): string
    {
        return $this->bloodType;
    }

    /**
     * @param string $bloodType
     * @return Volunteer
     */
    public function setBloodType(string $bloodType): Volunteer
    {
        $this->bloodType = $bloodType;
        return $this;
    }

    /**
     * @return int
     */
    public function getOccupation(): int
    {
        return $this->occupation;
    }

    /**
     * @param int $occupation
     * @return Volunteer
     */
    public function setOccupation(int $occupation): Volunteer
    {
        $this->occupation = $occupation;
        return $this;
    }

    /**
     * @return int
     */
    public function getEducationAttainment(): int
    {
        return $this->educationAttainment;
    }

    /**
     * @param int $educationAttainment
     * @return Volunteer
     */
    public function setEducationAttainment(int $educationAttainment): Volunteer
    {
        $this->educationAttainment = $educationAttainment;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactNumber(): string
    {
        return $this->contactNumber;
    }

    /**
     * @param string $contactNumber
     * @return Volunteer
     */
    public function setContactNumber(string $contactNumber): Volunteer
    {
        $this->contactNumber = $contactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return Volunteer
     */
    public function setEmailAddress(string $emailAddress): Volunteer
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomesticPartner(): ?string
    {
        return $this->domesticPartner;
    }

    /**
     * @param ?string $domesticPartner
     * @return Volunteer
     */
    public function setDomesticPartner(?string $domesticPartner): Volunteer
    {
        $this->domesticPartner = $domesticPartner;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecialSkill(): string
    {
        return $this->specialSkill;
    }

    /**
     * @param string $specialSkill
     * @return Volunteer
     */
    public function setSpecialSkill(string $specialSkill): Volunteer
    {
        $this->specialSkill = $specialSkill;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmergencyName(): string
    {
        return $this->emergencyName;
    }

    /**
     * @param string $emergencyName
     * @return Volunteer
     */
    public function setEmergencyName(string $emergencyName): Volunteer
    {
        $this->emergencyName = $emergencyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmergencyNumber(): string
    {
        return $this->emergencyNumber;
    }

    /**
     * @param string $emergencyNumber
     * @return Volunteer
     */
    public function setEmergencyNumber(string $emergencyNumber): Volunteer
    {
        $this->emergencyNumber = $emergencyNumber;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateAccomplished(): DateTimeImmutable
    {
        return $this->dateAccomplished;
    }

    /**
     * @param DateTimeImmutable $dateAccomplished
     * @return Volunteer
     */
    public function setDateAccomplished(DateTimeImmutable $dateAccomplished): Volunteer
    {
        $this->dateAccomplished = $dateAccomplished;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateSigned(): DateTimeImmutable
    {
        return $this->dateSigned;
    }

    /**
     * @param DateTimeImmutable $dateSigned
     * @return Volunteer
     */
    public function setDateSigned(DateTimeImmutable $dateSigned): Volunteer
    {
        $this->dateSigned = $dateSigned;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateAppointed(): ?DateTimeImmutable
    {
        return $this->dateAppointed;
    }

    /**
     * @param DateTimeImmutable|null $dateAppointed
     * @return Volunteer
     */
    public function setDateAppointed(?DateTimeImmutable $dateAppointed): Volunteer
    {
        $this->dateAppointed = $dateAppointed;
        return $this;
    }

    /**
     * @return string
     */
    public function getVpaStatus(): string
    {
        return $this->vpaStatus;
    }

    /**
     * @param string $vpaStatus
     * @return Volunteer
     */
    public function setVpaStatus(string $vpaStatus): Volunteer
    {
        $this->vpaStatus = $vpaStatus;
        return $this;
    }

    /**
      * @return string|null
      */
    public function getIdNumber(): ?string
    {
      return $this->idNumber;
    }

    /**
     * @param string|null $idNumber
     * @return Volunteer
     */
    public function setIdNumber(?string $idNumber): Volunteer
    {
      $this->idNumber = $idNumber;

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
