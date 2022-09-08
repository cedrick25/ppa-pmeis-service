<?php

namespace App\Entity;

use App\Repository\AuditTrailRepository;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuditTrailRepository::class)
 */
class AuditTrail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private Uuid $auditTrailId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $action;

    /**
     * @ORM\Column(type="integer")
     */
    private int $userId;

    /**
     * @var array<string, mixed>
     * @ORM\Column(type="json")
     */
    private array $actionDetails = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    public function getAuditTrailId(): Uuid
    {
        return $this->auditTrailId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getActionDetails(): array
    {
        return $this->actionDetails;
    }

    public function setActionDetails(array $actionDetails): self
    {
        $this->actionDetails = $actionDetails;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
