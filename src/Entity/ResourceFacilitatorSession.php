<?php

namespace App\Entity;

use App\Repository\ResourceFacilitatorSessionRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ResourceFacilitatorType;

/**
 * @ORM\Entity(repositoryClass=ResourceFacilitatorSessionRepository::class)
 */
class ResourceFacilitatorSession
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $resourceFacilitatorSessionId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sessionId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $resourceFacilitatorId;

    /**
     * @ORM\Column(type="string", length=3, columnDefinition="enum('PPO', 'VPA', 'ERP')")
     */
    private string $resourceFacilitatorType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $role;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $erpName;

    public function getResourceFacilitatorSessionId(): ?int
    {
        return $this->resourceFacilitatorSessionId;
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

    public function getResourceFacilitatorId(): ?int
    {
        return $this->resourceFacilitatorId;
    }

    public function setResourceFacilitatorId(int $resourceFacilitatorId): self
    {
        $this->resourceFacilitatorId = $resourceFacilitatorId;

        return $this;
    }

    public function getResourceFacilitatorType(): ?string
    {
        return $this->resourceFacilitatorType;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setResourceFacilitatorType(string $resourceFacilitatorType): self
    {
        if (! ResourceFacilitatorType::isValid($resourceFacilitatorType)) {
            throw new InvalidArgumentException("Invalid Resource Facilitator Type");
        }

        $this->resourceFacilitatorType = $resourceFacilitatorType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     * @return ResourceFacilitatorSession
     */
    public function setRole(?string $role): ResourceFacilitatorSession
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getErpName(): ?string
    {
        return $this->erpName;
    }

    /**
     * @param string|null $erpName
     * @return ResourceFacilitatorSession
     */
    public function setErpName(?string $erpName): ResourceFacilitatorSession
    {
        $this->erpName = $erpName;
        return $this;
    }
}
