<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Quarters as QuartersModel;

interface QuartersInterface
{
    public function createQuarters(QuartersModel $quarters): array;

    public function getAllQuarters(): array;

    public function deleteQuarterById(int $id): array;
}