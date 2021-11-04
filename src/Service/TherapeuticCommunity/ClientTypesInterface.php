<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\ClientTypes as ClientTypesModel;

interface ClientTypesInterface
{
    public function create(ClientTypesModel $clientTypes): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;
}