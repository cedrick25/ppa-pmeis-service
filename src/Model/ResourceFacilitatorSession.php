<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ResourceFacilitatorSession
{
    public function __construct(
        private int $sessionId,
        private int $resourceFacilitatorId,
        private string $resourceFacilitatorType
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
}