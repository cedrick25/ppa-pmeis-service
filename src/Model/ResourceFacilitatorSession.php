<?php

namespace App\Model;

use App\Common\AppHydrator;
use Symfony\Component\Validator\Constraints as Assert;

class ResourceFacilitatorSession implements \JsonSerializable
{
    public function __construct(
        private int $sessionId,
        private int $resourceFacilitatorId,
        private string $resourceFacilitatorType,
        private ?string $role,
        private ?string $erpName,
    ){}

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @return int
     */
    public function getResourceFacilitatorId(): int
    {
        return $this->resourceFacilitatorId;
    }

    /**
     * @Assert\NotBlank
     * @Assert\Length(3)
     * @return string
     */
    public function getResourceFacilitatorType(): string
    {
        return $this->resourceFacilitatorType;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @return string|null
     */
    public function getErpName(): ?string
    {
        return $this->erpName;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}