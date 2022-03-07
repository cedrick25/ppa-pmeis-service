<?php

namespace App\Entity;

use App\Enum\CapabilityBuildingSubType;
use App\Enum\IdSupportType;
use App\Repository\CapabilityBuildingRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CapabilityBuildingRepository::class)
 */
class CapabilityBuilding
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $capabilityBuildingId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $subtype;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="integer")
     */
    private int $noOfParticipants;

    /**
     * @ORM\Column(type="text")
     */
    private string $names;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPwd;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isSeniorCitizen;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $notManagerialSupervisory;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $notTechnical;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $notFoundation;

    /**
     * @ORM\Column(type="integer")
     */
    private int $noOfTrainingHours;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $tcInHouse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $tcOutHouse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remarks;

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

    public function getCapabilityBuildingId(): ?int
    {
        return $this->capabilityBuildingId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (! IdSupportType::isValid($type)) {
            throw new InvalidArgumentException("Invalid type should be Personnel/VPA");
        }

        $this->type = $type;

        return $this;
    }

    public function getSubtype(): ?string
    {
        return $this->subtype;
    }

    public function setSubtype(string $subtype): self
    {
        if (! CapabilityBuildingSubType::isValid($subtype)) {
            throw new InvalidArgumentException("Invalid sub type");
        }

        $this->subtype = $subtype;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getNoOfParticipants(): ?int
    {
        return $this->noOfParticipants;
    }

    public function setNoOfParticipants(int $noOfParticipants): self
    {
        $this->noOfParticipants = $noOfParticipants;

        return $this;
    }

    public function getNames(): ?string
    {
        return $this->names;
    }

    public function setNames(string $names): self
    {
        $this->names = $names;

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

    public function getIsSeniorCitizen(): ?bool
    {
        return $this->isSeniorCitizen;
    }

    public function setIsSeniorCitizen(bool $isSeniorCitizen): self
    {
        $this->isSeniorCitizen = $isSeniorCitizen;

        return $this;
    }

    public function getNotManagerialSupervisory(): ?string
    {
        return $this->notManagerialSupervisory;
    }

    public function setNotManagerialSupervisory(?string $notManagerialSupervisory): self
    {
        $this->notManagerialSupervisory = $notManagerialSupervisory;

        return $this;
    }

    public function getNotTechnical(): ?string
    {
        return $this->notTechnical;
    }

    public function setNotTechnical(?string $notTechnical): self
    {
        $this->notTechnical = $notTechnical;

        return $this;
    }

    public function getNotFoundation(): ?string
    {
        return $this->notFoundation;
    }

    public function setNotFoundation(?string $notFoundation): self
    {
        $this->notFoundation = $notFoundation;

        return $this;
    }

    public function getNoOfTrainingHours(): ?int
    {
        return $this->noOfTrainingHours;
    }

    public function setNoOfTrainingHours(int $noOfTrainingHours): self
    {
        $this->noOfTrainingHours = $noOfTrainingHours;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTcInHouse(): ?string
    {
        return $this->tcInHouse;
    }

    /**
     * @param string|null $tcInHouse
     * @return CapabilityBuilding
     */
    public function setTcInHouse(?string $tcInHouse): CapabilityBuilding
    {
        $this->tcInHouse = $tcInHouse;
        return $this;
    }

    public function getTcOutHouse(): ?string
    {
        return $this->tcOutHouse;
    }

    public function setTcOutHouse(?string $tcOutHouse): self
    {
        $this->tcOutHouse = $tcOutHouse;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    public function setFieldOfficeId(int $fieldOfficeId): self
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
