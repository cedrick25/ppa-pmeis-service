<?php

namespace App\Model;

use App\Common\AppHydrator;

class ClientSessions implements \JsonSerializable
{
    public function __construct(
        private int $clientId,
        private int $sessionId,
        private string $role
    ){}

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @return int
     */
    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}