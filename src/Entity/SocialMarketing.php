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
    private string $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    /**
     * @ORM\Column(type="integer")
     */
    private int $primers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fieldOfficeId;

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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
    public function getPrimers(): int
    {
        return $this->primers;
    }

    /**
     * @param int $primers
     */
    public function setPrimers(int $primers): void
    {
        $this->primers = $primers;
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
     * @return SocialMarketing
     */
    public function setFieldOfficeId(?int $fieldOfficeId): SocialMarketing
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
