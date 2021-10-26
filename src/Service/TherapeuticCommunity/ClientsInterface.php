<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Clients;

interface ClientsInterface
{
    public function create(Clients $clientData): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, Clients $clientData):array;
}