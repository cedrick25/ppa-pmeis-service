<?php

namespace App\Entity;

use App\Repository\JailDecongestionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JailDecongestionRepository::class)
 */
class JailDecongestion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $jailDecongestionId;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $nameAddress;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $jailVenue;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $jailOffice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $probation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $clemency;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $referralPao;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $referralProsecution;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $referralOthers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $gcta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $recognizance;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remarks;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $fieldOfficeId;

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

    public function getJailDecongestionId(): ?int
    {
        return $this->jailDecongestionId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     * @return JailDecongestion
     */
    public function setDate(\DateTimeInterface $date): JailDecongestion
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameAddress(): ?string
    {
        return $this->nameAddress;
    }

    /**
     * @param string|null $nameAddress
     * @return JailDecongestion
     */
    public function setNameAddress(?string $nameAddress): JailDecongestion
    {
        $this->nameAddress = $nameAddress;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJailVenue(): bool
    {
        return $this->jailVenue;
    }

    /**
     * @param bool $jailVenue
     * @return JailDecongestion
     */
    public function setJailVenue(bool $jailVenue): JailDecongestion
    {
        $this->jailVenue = $jailVenue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJailOffice(): bool
    {
        return $this->jailOffice;
    }

    /**
     * @param bool $jailOffice
     * @return JailDecongestion
     */
    public function setJailOffice(bool $jailOffice): JailDecongestion
    {
        $this->jailOffice = $jailOffice;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProbation(): ?int
    {
        return $this->probation;
    }

    /**
     * @param int|null $probation
     * @return JailDecongestion
     */
    public function setProbation(?int $probation): JailDecongestion
    {
        $this->probation = $probation;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getClemency(): ?int
    {
        return $this->clemency;
    }

    /**
     * @param int|null $clemency
     * @return JailDecongestion
     */
    public function setClemency(?int $clemency): JailDecongestion
    {
        $this->clemency = $clemency;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReferralPao(): ?int
    {
        return $this->referralPao;
    }

    /**
     * @param int|null $referralPao
     * @return JailDecongestion
     */
    public function setReferralPao(?int $referralPao): JailDecongestion
    {
        $this->referralPao = $referralPao;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReferralProsecution(): ?int
    {
        return $this->referralProsecution;
    }

    /**
     * @param int|null $referralProsecution
     * @return JailDecongestion
     */
    public function setReferralProsecution(?int $referralProsecution): JailDecongestion
    {
        $this->referralProsecution = $referralProsecution;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReferralOthers(): ?int
    {
        return $this->referralOthers;
    }

    /**
     * @param int|null $referralOthers
     * @return JailDecongestion
     */
    public function setReferralOthers(?int $referralOthers): JailDecongestion
    {
        $this->referralOthers = $referralOthers;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGcta(): ?int
    {
        return $this->gcta;
    }

    /**
     * @param int|null $gcta
     * @return JailDecongestion
     */
    public function setGcta(?int $gcta): JailDecongestion
    {
        $this->gcta = $gcta;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecognizance(): ?int
    {
        return $this->recognizance;
    }

    /**
     * @param int|null $recognizance
     * @return JailDecongestion
     */
    public function setRecognizance(?int $recognizance): JailDecongestion
    {
        $this->recognizance = $recognizance;
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
     * @return JailDecongestion
     */
    public function setRemarks(string $remarks): JailDecongestion
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
     * @return JailDecongestion
     */
    public function setFieldOfficeId(int $fieldOfficeId): JailDecongestion
    {
        $this->fieldOfficeId = $fieldOfficeId;
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
     * @return JailDecongestion
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): JailDecongestion
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
     * @return JailDecongestion
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): JailDecongestion
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
     * @return JailDecongestion
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): JailDecongestion
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
