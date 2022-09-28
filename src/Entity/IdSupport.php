<?php

namespace App\Entity;

use App\Enum\IdSupportType;
use App\Repository\IdSupportRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IdSupportRepository::class)
 */
class IdSupport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="integer")
     */
    private int $vpaPersonnelId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $program;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $activity;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $venue;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $assistanceRendered;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setType(string $type): self
    {
        if (! IdSupportType::isValid($type)) {
            throw new InvalidArgumentException("Invalid ID Type");
        }
        $this->type = $type;

        return $this;
    }

    public function getVpaPersonnelId(): ?int
    {
        return $this->vpaPersonnelId;
    }

    public function setVpaPersonnelId(int $vpaPersonelId): self
    {
        $this->vpaPersonnelId = $vpaPersonelId;

        return $this;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function setProgram(string $program): self
    {
        $this->program = $program;

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

    /**
     * @return string
     */
    public function getActivity(): string
    {
        return $this->activity;
    }

    /**
     * @param string $activity
     * @return IdSupport
     */
    public function setActivity(string $activity): IdSupport
    {
        $this->activity = $activity;
        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
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

    public function getAssistanceRendered(): ?string
    {
        return $this->assistanceRendered;
    }

    public function setAssistanceRendered(string $assistanceRendered): self
    {
        $this->assistanceRendered = $assistanceRendered;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return IdSupport
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): IdSupport
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeImmutable|null $updatedAt
     * @return IdSupport
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): IdSupport
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeImmutable|null $deletedAt
     * @return IdSupport
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): IdSupport
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
