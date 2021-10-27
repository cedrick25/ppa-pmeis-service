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
     * @ORM\Column(type="string", length=255, columnDefinition="enum('PS', 'PR', 'PD', 'JICL', 'FTMDO', 'PET', 'TERM')")
     */
    private string $role;

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
}
