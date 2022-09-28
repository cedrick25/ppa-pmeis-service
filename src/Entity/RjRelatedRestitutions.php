<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RJGroup;
use App\Repository\RjRelatedRestitutionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RjRelatedRestitutionsRepository::class)
 */
class RjRelatedRestitutions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $rjRelatedRestitutionId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quarterId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fieldOfficeId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $rjGroup;

    /**
     * @ORM\Column(type="integer")
     */
    private int $offenseId;

    /**
     * @ORM\Column(type="float")
     */
    private float $originalAmount;

    /**
     * @ORM\Column(type="float")
     */
    private float $startOfQuarter;

    /**
     * @ORM\Column(type="float")
     */
    private float $amountPaid;

    /**
     * @ORM\Column(type="float")
     */
    private float $balance;

    /**
     * @ORM\Column(type="integer")
     */
    private int $paymentFormId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $paymentModeId;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $paymentDate;

    /**
     * @ORM\Column(type="float")
     */
    private float $paymentAmount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $paymentRecipient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $remittedTo;

    /**
     * @ORM\Column(type="float")
     */
    private float $remittedAmount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remarks;

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

    public function getRjRelatedRestitutionId(): ?int
    {
        return $this->rjRelatedRestitutionId;
    }

    public function getQuarterId(): ?int
    {
        return $this->quarterId;
    }

    public function setQuarterId(int $quarterId): self
    {
        $this->quarterId = $quarterId;

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

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getRjGroup(): ?string
    {
        return $this->rjGroup;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setRjGroup(string $rjGroup): self
    {
        if (! RJGroup::isValid($rjGroup)) {
            throw new InvalidArgumentException("Invalid RJ Group");
        }
        $this->rjGroup = $rjGroup;

        return $this;
    }

    public function getOffenseId(): ?int
    {
        return $this->offenseId;
    }

    public function setOffenseId(int $offenseId): self
    {
        $this->offenseId = $offenseId;

        return $this;
    }

    public function getOriginalAmount(): ?float
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(float $originalAmount): self
    {
        $this->originalAmount = $originalAmount;

        return $this;
    }

    public function getStartOfQuarter(): ?float
    {
        return $this->startOfQuarter;
    }

    public function setStartOfQuarter(float $startOfQuarter): self
    {
        $this->startOfQuarter = $startOfQuarter;

        return $this;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(float $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getPaymentFormId(): ?int
    {
        return $this->paymentFormId;
    }

    public function setPaymentFormId(int $paymentFormId): self
    {
        $this->paymentFormId = $paymentFormId;

        return $this;
    }

    public function getPaymentModeId(): ?int
    {
        return $this->paymentModeId;
    }

    public function setPaymentModeId(int $paymentModeId): self
    {
        $this->paymentModeId = $paymentModeId;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getPaymentDate(): ?\DateTimeImmutable
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTimeImmutable|null $paymentDate
     * @return RjRelatedRestitutions
     */
    public function setPaymentDate(?\DateTimeImmutable $paymentDate): RjRelatedRestitutions
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getPaymentAmount(): ?float
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount(float $paymentAmount): self
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    public function getPaymentRecipient(): ?string
    {
        return $this->paymentRecipient;
    }

    public function setPaymentRecipient(string $paymentRecipient): self
    {
        $this->paymentRecipient = $paymentRecipient;

        return $this;
    }

    public function getRemittedTo(): ?string
    {
        return $this->remittedTo;
    }

    public function setRemittedTo(string $remittedTo): self
    {
        $this->remittedTo = $remittedTo;

        return $this;
    }

    public function getRemittedAmount(): ?float
    {
        return $this->remittedAmount;
    }

    public function setRemittedAmount(float $remittedAmount): self
    {
        $this->remittedAmount = $remittedAmount;

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
     * @return RjRelatedRestitutions
     */
    public function setRemarks(?string $remarks): RjRelatedRestitutions
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
