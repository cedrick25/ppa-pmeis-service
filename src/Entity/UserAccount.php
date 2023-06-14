<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserAccountRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Enum\UserType;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * @ORM\Entity(repositoryClass=UserAccountRepository::class)
 * @method string getUserIdentifier()
 */
class UserAccount implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $userAccountId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $emailAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $contactNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private string $userType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $regionId;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    private int $status;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $deletedAt;

    public function getUserAccountId(): ?int
    {
        return $this->userAccountId;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }

    public function setContactNumber(?string $contactNumber): self
    {
        $this->contactNumber = $contactNumber;

        return $this;
    }

    public function (): ?string
    {
        if ($this->getDeletedAt() !== null) {
            return null;
        }

        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setUserType(string $userType): self
    {
        if (! UserType::isValid($userType)) {
            throw new InvalidArgumentException("Invalid User Type");
        }
        $this->userType = $userType;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): self
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

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $userRoles = ['FIELD_OFFICER'];

        if ($this->getFieldOfficeId() !== null) {
            $userRoles['field_office_id'] = $this->getFieldOfficeId();
        }

        if ($this->getRegionId() !== null) {
            $userRoles['region_id'] = $this->getRegionId();
        }

        if ($this->getUserType() == UserType::CSD) {
            $userRoles[] = 'CSD';
        }

        if ($this->getUserType() == UserType::RD) {
            $userRoles[] = 'REGIONAL_DIRECTOR';
        }

        if ($this->getUserType() == UserType::ND) {
            $userRoles[] = 'REGIONAL_DIRECTOR';
            $userRoles[] = 'NATIONAL_DIRECTOR';
        }

        return $userRoles;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername(): string
    {
        return $this->getEmailAddress();
    }
}
