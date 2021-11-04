<?php

namespace App\Service\TherapeuticCommunity;

interface FieldOfficesInterface
{
    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getByRegion(int $regionId): array;

    public function getById(int $id): array;
}