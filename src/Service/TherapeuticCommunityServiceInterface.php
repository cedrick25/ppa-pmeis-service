<?php

namespace App\Service;

use App\Model\Quarters as QuartersModel;

interface TherapeuticCommunityServiceInterface
{
    public function createQuarters(QuartersModel $quarters): array;

    public function getAllQuarters(): array;

    public function deleteQuarterById(int $id): array;
}