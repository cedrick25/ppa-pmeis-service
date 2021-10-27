<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\ClientSessions;

interface ClientSessionsInterface
{
    public function create(ClientSessions $clientSessions): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, ClientSessions $clientSessions):array;
}