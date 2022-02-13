<?php

namespace App\Entity;

use App\Enum\SourceType;
use App\Enum\UtilizedFor;
use App\Repository\ResourceMobilizationRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResourceMobilizationRepository::class)
 */
class ResourceMobilization
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $resourceMobilizationId;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('TC', 'RJ', 'VPA', 'GAD', 'OTHERS')")
     */
    private string $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $activityName;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $venue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $amount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $cashSourceName;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('GO', 'NGO', 'IND')")
     */
    private string $cashSourceType;

    /**
     * @ORM\Column(type="integer")
     */
    private int $materialsId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $materialsQty;

    /**
     * @ORM\Column(type="float")
     */
    private float $materialsAmount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $materialSourceName;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('GO', 'NGO', 'IND')")
     */
    private string $materialSourceType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $technicalAssistanceParticulars;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $technicalAssistanceAmount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $technicalAssistanceName;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="enum('GO', 'NGO', 'IND')")
     */
    private string $technicalAssistanceType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $resourcesSecuredBy;

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

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    public function getResourceMobilizationId(): ?int
    {
        return $this->resourceMobilizationId;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCategory(string $category): self
    {
        if (! UtilizedFor::isValid($category)) {
            throw new InvalidArgumentException("Invalid Category For");
        }
        $this->category = $category;

        return $this;
    }

    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function setActivityName(string $activityName): self
    {
        $this->activityName = $activityName;

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

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(string $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCashSourceName(): ?string
    {
        return $this->cashSourceName;
    }

    public function setCashSourceName(string $cashSourceName): self
    {
        $this->cashSourceName = $cashSourceName;

        return $this;
    }

    public function getCashSourceType(): ?string
    {
        return $this->cashSourceType;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCashSourceType(string $cashSourceType): self
    {
        if (! SourceType::isValid($cashSourceType)) {
            throw new InvalidArgumentException("Invalid Source Type For");
        }
        $this->cashSourceType = $cashSourceType;

        return $this;
    }

    public function getMaterialsId(): ?int
    {
        return $this->materialsId;
    }

    public function setMaterialsId(int $materialsId): self
    {
        $this->materialsId = $materialsId;

        return $this;
    }

    public function getMaterialsQty(): ?int
    {
        return $this->materialsQty;
    }

    public function setMaterialsQty(int $materialsQty): self
    {
        $this->materialsQty = $materialsQty;

        return $this;
    }

    public function getMaterialSourceName(): ?string
    {
        return $this->materialSourceName;
    }

    public function setMaterialSourceName(string $materialSourceName): self
    {
        $this->materialSourceName = $materialSourceName;

        return $this;
    }

    /**
     * @return float
     */
    public function getMaterialsAmount(): float
    {
        return $this->materialsAmount;
    }

    /**
     * @param float $materialsAmount
     * @return ResourceMobilization
     */
    public function setMaterialsAmount(float $materialsAmount): ResourceMobilization
    {
        $this->materialsAmount = $materialsAmount;
        return $this;
    }

    public function getMaterialSourceType(): ?string
    {
        return $this->materialSourceType;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setMaterialSourceType(string $materialSourceType): self
    {
        if (! SourceType::isValid($materialSourceType)) {
            throw new InvalidArgumentException("Invalid Source Type For");
        }
        $this->materialSourceType = $materialSourceType;

        return $this;
    }

    public function getTechnicalAssistanceParticulars(): ?string
    {
        return $this->technicalAssistanceParticulars;
    }

    public function setTechnicalAssistanceParticulars(string $technicalAssistanceParticulars): self
    {
        $this->technicalAssistanceParticulars = $technicalAssistanceParticulars;

        return $this;
    }

    public function getTechnicalAssistanceAmount(): ?float
    {
        return $this->technicalAssistanceAmount;
    }

    public function setTechnicalAssistanceAmount(?float $technicalAssistanceAmount): self
    {
        $this->technicalAssistanceAmount = $technicalAssistanceAmount;

        return $this;
    }

    public function getTechnicalAssistanceName(): ?string
    {
        return $this->technicalAssistanceName;
    }

    public function setTechnicalAssistanceName(string $technicalAssistanceName): self
    {
        $this->technicalAssistanceName = $technicalAssistanceName;

        return $this;
    }

    public function getTechnicalAssistanceType(): ?string
    {
        return $this->technicalAssistanceType;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setTechnicalAssistanceType(string $technicalAssistanceType): self
    {
        if (! SourceType::isValid($technicalAssistanceType)) {
            throw new InvalidArgumentException("Invalid Source Type For");
        }
        $this->technicalAssistanceType = $technicalAssistanceType;

        return $this;
    }

    public function getResourcesSecuredBy(): ?string
    {
        return $this->resourcesSecuredBy;
    }

    public function setResourcesSecuredBy(string $resourcesSecuredBy): self
    {
        $this->resourcesSecuredBy = $resourcesSecuredBy;

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

    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    public function setFieldOfficeId(int $fieldOfficeId): self
    {
        $this->fieldOfficeId = $fieldOfficeId;

        return $this;
    }
}
