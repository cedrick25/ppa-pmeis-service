<?php

namespace App\Entity;

use App\Repository\UserDetailsRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserDetailsRepository::class)
 */
class UserDetails
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $userDetailId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $userAccountId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $middleName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $lastName;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private string $suffix;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private string $gender;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private DateTimeImmutable $dateOfBirth;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isSeniorCitizen;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPwd;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $updatedAt;

    public function getUserDetailId(): ?int
    {
        return $this->userDetailId;
    }

    public function setUserDetailId(int $userDetailId): self
    {
        $this->userDetailId = $userDetailId;

        return $this;
    }

    public function getUserAccountId(): ?int
    {
        return $this->userAccountId;
    }

    public function setUserAccountId(int $userAccountId): self
    {
        $this->userAccountId = $userAccountId;

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

    public function getDateOfBirth(): ?DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(DateTimeImmutable $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

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

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
