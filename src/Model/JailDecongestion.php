<?php

namespace App\Model;

use App\Common\AppHydrator;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JailDecongestion implements \JsonSerializable
{
    public function __construct(
        private string $date,
        private bool $jailVenue,
        private bool $jailOffice,
        private ?int $probation,
        private ?int $clemency,
        private ?int $referralPao,
        private ?int $referralProsecution,
        private ?int $referralOthers,
        private ?int $gcta,
        private ?int $recognizance,
        private array $personResponsible,
        private string $remarks,
        private int $fieldOfficeId,
        private ?string $nameAddress = null,
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
        private ?DateTimeInterface $deletedAt = null
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getNameAddress(): ?string
    {
        return $this->nameAddress;
    }

    /**
     * @return bool
     */
    public function isJailVenue(): bool
    {
        return $this->jailVenue;
    }

    /**
     * @return bool
     */
    public function isJailOffice(): bool
    {
        return $this->jailOffice;
    }

    /**
     * @return int|null
     */
    public function getProbation(): ?int
    {
        return $this->probation;
    }

    /**
     * @return int|null
     */
    public function getClemency(): ?int
    {
        return $this->clemency;
    }

    /**
     * @return int|null
     */
    public function getReferralPao(): ?int
    {
        return $this->referralPao;
    }

    /**
     * @return int|null
     */
    public function getReferralProsecution(): ?int
    {
        return $this->referralProsecution;
    }

    /**
     * @return int|null
     */
    public function getReferralOthers(): ?int
    {
        return $this->referralOthers;
    }

    /**
     * @return int|null
     */
    public function getGcta(): ?int
    {
        return $this->gcta;
    }

    /**
     * @return int|null
     */
    public function getRecognizance(): ?int
    {
        return $this->recognizance;
    }

    /**
     * @return array
     */
    public function getPersonResponsible(): array
    {
        return $this->personResponsible;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRemarks(): string
    {
        return $this->remarks;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getFieldOfficeId(): int
    {
        return $this->fieldOfficeId;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}