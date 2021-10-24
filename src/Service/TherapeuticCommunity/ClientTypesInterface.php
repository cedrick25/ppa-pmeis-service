<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\ClientTypes as ClientTypesModel;

interface ClientTypesInterface
{
    public function create(ClientTypesModel $clientTypes): array;

    public function getAll(): array;

    public function deleteById(int $id): array;
}