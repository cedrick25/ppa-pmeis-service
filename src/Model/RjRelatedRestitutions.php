<?php

declare(strict_types=1);

namespace App\Model;
use Symfony\Component\Validator\Constraints as Assert;

class RjRelatedRestitutions
{
    public function __construct(
        private int $quarterId,
        private int $fieldOfficeId,
        private int $clientId,
        private string $rjGroup,
        private int $offenseId,
        private float $originalAmount,
        private float $startOfQuarter,
        private float $amountPaid,
        private float $balance,
        private int $paymentFormId,
        private int $paymentModeId,
        private string $paymentDate,
        private float $paymentAmount,
        private string $paymentRecipient,
        private string $remittedTo,
        private float $remittedAmount,
        private ?string $remarks = null,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
        private ?\DateTimeImmutable $deletedAt = null,
    ){}

    /**
     * @return int
     */
    public function getQuarterId(): int
    {
        return $this->quarterId;
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
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRjGroup(): string
    {
        return $this->rjGroup;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getOffenseId(): int
    {
        return $this->offenseId;
    }

    /**
     * @Assert\NotBlank
     * @return float
     */
    public function getOriginalAmount(): float
    {
        return $this->originalAmount;
    }

    /**
     * @Assert\NotBlank
     * @return float
     */
    public function getStartOfQuarter(): float
    {
        return $this->startOfQuarter;
    }

    /**
     * @Assert\NotBlank
     * @return float
     */
    public function getAmountPaid(): float
    {
        return $this->amountPaid;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getPaymentFormId(): int
    {
        return $this->paymentFormId;
    }

    /**
     * @Assert\NotBlank
     * @return int
     */
    public function getPaymentModeId(): int
    {
        return $this->paymentModeId;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }

    /**
     * @Assert\NotBlank
     * @return float
     */
    public function getPaymentAmount(): float
    {
        return $this->paymentAmount;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getPaymentRecipient(): string
    {
        return $this->paymentRecipient;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getRemittedTo(): string
    {
        return $this->remittedTo;
    }

    /**
     * @Assert\NotBlank
     * @return float
     */
    public function getRemittedAmount(): float
    {
        return $this->remittedAmount;
    }

    /**
     * @return string|null
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}