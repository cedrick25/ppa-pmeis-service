<?php

namespace App\Entity;

use App\Repository\SocialMarketingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocialMarketingRepository::class)
 */
class SocialMarketing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $socialMarketingId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $socialMarketingActivityId;

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
     * @ORM\Column(type="string", length=255)
     */
    private string $participants;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $personnelId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $personnelRole;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $vpaId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vpaRole;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

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

    public function getSocialMarketingId(): ?int
    {
        return $this->socialMarketingId;
    }

    public function getSocialMarketingActivityId(): ?int
    {
        return $this->socialMarketingActivityId;
    }

    public function setSocialMarketingActivityId(int $socialMarketingActivityId): self
    {
        $this->socialMarketingActivityId = $socialMarketingActivityId;

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

    public function getParticipants(): ?string
    {
        return $this->participants;
    }

    public function setParticipants(string $participants): self
    {
        $this->participants = $participants;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPersonnelId(): ?int
    {
        return $this->personnelId;
    }

    /**
     * @param int|null $personnelId
     * @return SocialMarketing
     */
    public function setPersonnelId(?int $personnelId): SocialMarketing
    {
        $this->personnelId = $personnelId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPersonnelRole(): ?string
    {
        return $this->personnelRole;
    }

    /**
     * @param string|null $personnelRole
     * @return SocialMarketing
     */
    public function setPersonnelRole(?string $personnelRole): SocialMarketing
    {
        $this->personnelRole = $personnelRole;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVpaId(): ?int
    {
        return $this->vpaId;
    }

    /**
     * @param int|null $vpaId
     * @return SocialMarketing
     */
    public function setVpaId(?int $vpaId): SocialMarketing
    {
        $this->vpaId = $vpaId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVpaRole(): ?string
    {
        return $this->vpaRole;
    }

    /**
     * @param string|null $vpaRole
     * @return SocialMarketing
     */
    public function setVpaRole(?string $vpaRole): SocialMarketing
    {
        $this->vpaRole = $vpaRole;
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
