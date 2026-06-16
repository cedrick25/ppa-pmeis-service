<?php

namespace App\Entity;

use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VpaAssociationInitiatedActivitiesRepository::class)
 */
class  VpaAssociationInitiatedActivities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $vpaAssociationInitiatedActivityId;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private int $serviceRenderedId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeInterface $venueDate;

    /**
     * @ORM\Column(type="integer")
     */
    private int $venueId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $crdResourcesTapped;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $crdAssistanceReceived;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quarterId;

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

    public function getVpaAssociationInitiatedActivityId(): ?int
    {
        return $this->vpaAssociationInitiatedActivityId;
    }

    /**
     * @return int
     */
    public function getServiceRenderedId(): int
    {
        return $this->serviceRenderedId;
    }

    /**
     * @param int $serviceRenderedId
     * @return VpaAssociationInitiatedActivities
     */
    public function setServiceRenderedId(int $serviceRenderedId): VpaAssociationInitiatedActivities
    {
        $this->serviceRenderedId = $serviceRenderedId;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getVenueDate(): \DateTimeInterface
    {
        return $this->venueDate;
    }

    /**
     * @param \DateTimeInterface $venueDate
     * @return VpaAssociationInitiatedActivities
     */
    public function setVenueDate(\DateTimeInterface $venueDate): VpaAssociationInitiatedActivities
    {
        $this->venueDate = $venueDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getVenueId(): int
    {
        return $this->venueId;
    }

    /**
     * @param int $venueId
     * @return VpaAssociationInitiatedActivities
     */
    public function setVenueId(int $venueId): VpaAssociationInitiatedActivities
    {
        $this->venueId = $venueId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCrdResourcesTapped(): ?string
    {
        return $this->crdResourcesTapped;
    }

    /**
     * @param string|null $crdResourcesTapped
     * @return VpaAssociationInitiatedActivities
     */
    public function setCrdResourcesTapped(?string $crdResourcesTapped): VpaAssociationInitiatedActivities
    {
        $this->crdResourcesTapped = $crdResourcesTapped;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCrdAssistanceReceived(): ?string
    {
        return $this->crdAssistanceReceived;
    }

    /**
     * @param string|null $crdAssistanceReceived
     * @return VpaAssociationInitiatedActivities
     */
    public function setCrdAssistanceReceived(?string $crdAssistanceReceived): VpaAssociationInitiatedActivities
    {
        $this->crdAssistanceReceived = $crdAssistanceReceived;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemarks(): string
    {
        return $this->remarks;
    }

    /**
     * @param string $remarks
     * @return VpaAssociationInitiatedActivities
     */
    public function setRemarks(string $remarks): VpaAssociationInitiatedActivities
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
     * @return VpaAssociationInitiatedActivities
     */
    public function setFieldOfficeId(int $fieldOfficeId): VpaAssociationInitiatedActivities
    {
        $this->fieldOfficeId = $fieldOfficeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuarterId(): int
    {
        return $this->quarterId;
    }

    /**
     * @param int $quarterId
     * @return VpaAssociationInitiatedActivities
     */
    public function setQuarterId(int $quarterId): VpaAssociationInitiatedActivities
    {
        $this->quarterId = $quarterId;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     * @return VpaAssociationInitiatedActivities
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): VpaAssociationInitiatedActivities
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable|null $updatedAt
     * @return VpaAssociationInitiatedActivities
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): VpaAssociationInitiatedActivities
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeImmutable|null $deletedAt
     * @return VpaAssociationInitiatedActivities
     */
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): VpaAssociationInitiatedActivities
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
