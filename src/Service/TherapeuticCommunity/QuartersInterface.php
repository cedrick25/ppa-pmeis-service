<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Quarters as QuartersModel;

interface QuartersInterface
{
    public function create(QuartersModel $quarters): array;

    public function getAll(): array;

    public function deleteById(int $id): array;
}