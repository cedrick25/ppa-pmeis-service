<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Clients;

interface ClientsInterface
{
    public function create(Clients $clientData): array;

    public function getAll(): array;

    public function getAllByFieldOffice(int $listByFieldOffice, int $clientTypeId): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, Clients $clientData):array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;

    public function getByClientId(int $id): array;
}