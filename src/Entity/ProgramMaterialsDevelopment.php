<?php

namespace App\Entity;

use App\Enum\UtilizedFor;
use App\Repository\ProgramMaterialsDevelopmentRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProgramMaterialsDevelopmentRepository::class)
 */
class ProgramMaterialsDevelopment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $programMaterialsDevelopmentId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $particulars;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $personResponsibleType;

    /**
     * @ORM\Column(type="integer")
     */
    private int $vpaPpoId;

    /**
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $utilizedFor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $deletedAt;

    public function getProgramMaterialsDevelopmentId(): ?int
    {
        return $this->programMaterialsDevelopmentId;
    }

    public function getParticulars(): ?string
    {
        return $this->particulars;
    }

    public function setParticulars(string $particulars): self
    {
        $this->particulars = $particulars;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPersonResponsibleType(): ?string
    {
        return $this->personResponsibleType;
    }

    public function setPersonResponsibleType(string $personResponsibleType): self
    {
        $this->personResponsibleType = $personResponsibleType;

        return $this;
    }

    public function getVpaPpoId(): ?int
    {
        return $this->vpaPpoId;
    }

    public function setVpaPpoId(int $vpaPpoId): self
    {
        $this->vpaPpoId = $vpaPpoId;

        return $this;
    }

    public function getUtilizedFor(): ?string
    {
        return $this->utilizedFor;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setUtilizedFor(string $utilizedFor): self
    {
        if (! UtilizedFor::isValid($utilizedFor)) {
            throw new InvalidArgumentException("Invalid Utilized For");
        }
        $this->utilizedFor = $utilizedFor;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @param int $fieldOfficeId
     * @return ProgramMaterialsDevelopment
     */
    public function setFieldOfficeId(int $fieldOfficeId): ProgramMaterialsDevelopment
    {
        $this->fieldOfficeId = $fieldOfficeId;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
