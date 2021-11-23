<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Quarters as QuartersModel;

interface QuartersInterface
{
    public function create(QuartersModel $quarters): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, QuartersModel $quarterData): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;

    public function searchPaginated(string $field, string $query,int $page, int $pageSize): array;
}