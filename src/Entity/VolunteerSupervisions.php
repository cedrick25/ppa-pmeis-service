<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VolunteerSupervisionsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VolunteerSupervisionsRepository::class)
 */
class VolunteerSupervisions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $volunteerSupervisionsId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $volunteerId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $servicesRenderedId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $communityResourcesTapped;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $assistanceReceived;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remarks;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer", nullable=true)
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

    public function getVolunteerSupervisionsId(): ?int
    {
        return $this->volunteerSupervisionsId;
    }

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
    }

    public function setVolunteerId(int $volunteerId): self
    {
        $this->volunteerId = $volunteerId;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getServicesRenderedId(): ?int
    {
        return $this->servicesRenderedId;
    }

    public function setServicesRenderedId(int $servicesRenderedId): self
    {
        $this->servicesRenderedId = $servicesRenderedId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommunityResourcesTapped(): ?string
    {
        return $this->communityResourcesTapped;
    }

    /**
     * @param string|null $communityResourcesTapped
     * @return VolunteerSupervisions
     */
    public function setCommunityResourcesTapped(?string $communityResourcesTapped): VolunteerSupervisions
    {
        $this->communityResourcesTapped = $communityResourcesTapped;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAssistanceReceived(): ?string
    {
        return $this->assistanceReceived;
    }

    /**
     * @param string|null $assistanceReceived
     * @return VolunteerSupervisions
     */
    public function setAssistanceReceived(?string $assistanceReceived): VolunteerSupervisions
    {
        $this->assistanceReceived = $assistanceReceived;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * @param string|null $remarks
     * @return VolunteerSupervisions
     */
    public function setRemarks(?string $remarks): VolunteerSupervisions
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFieldOfficeId(): ?int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @param int|null $fieldOfficeId
     * @return VolunteerSupervisions
     */
    public function setFieldOfficeId(?int $fieldOfficeId): VolunteerSupervisions
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
     * @return VolunteerSupervisions
     */
    public function setQuarterId(int $quarterId): VolunteerSupervisions
    {
        $this->quarterId = $quarterId;
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
