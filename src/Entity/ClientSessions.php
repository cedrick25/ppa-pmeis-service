<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientSessionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ClientSessionRole;

/**
 * @ORM\Entity(repositoryClass=ClientSessionsRepository::class)
 */
class ClientSessions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $clientSessionId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $clientId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sessionId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $role;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $clientRemarksId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $otherRemarks;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $fsi;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $remarksDate;

    public function getClientSessionId(): ?int
    {
        return $this->clientSessionId;
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

    public function getSessionId(): ?int
    {
        return $this->sessionId;
    }

    public function setSessionId(int $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setRole(string $role): self
    {
        if (! ClientSessionRole::isValid($role)) {
            throw new InvalidArgumentException("Invalid Role");
        }
        $this->role = $role;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getClientRemarksId(): ?int
    {
        return $this->clientRemarksId;
    }

    /**
     * @param int|null $clientRemarksId
     * @return ClientSessions
     */
    public function setClientRemarksId(?int $clientRemarksId): ClientSessions
    {
        $this->clientRemarksId = $clientRemarksId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOtherRemarks(): ?string
    {
        return $this->otherRemarks;
    }

    /**
     * @param string|null $otherRemarks
     * @return ClientSessions
     */
    public function setOtherRemarks(?string $otherRemarks): ClientSessions
    {
        $this->otherRemarks = $otherRemarks;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFsi(): ?bool
    {
        return $this->fsi;
    }

    /**
     * @param bool|null $fsi
     * @return ClientSessions
     */
    public function setFsi(?bool $fsi): ClientSessions
    {
        $this->fsi = $fsi;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRemarksDate(): ?\DateTimeInterface
    {
        return $this->remarksDate;
    }

    /**
     * @param \DateTimeInterface|null $remarksDate
     * @return ClientSessions
     */
    public function setRemarksDate(?\DateTimeInterface $remarksDate): ClientSessions
    {
        $this->remarksDate = $remarksDate;
        return $this;
    }
}
