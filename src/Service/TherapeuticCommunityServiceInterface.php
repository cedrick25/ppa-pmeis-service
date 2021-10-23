<?php

namespace App\Service;

use App\Model\Quarters as QuartersModel;

interface TherapeuticCommunityServiceInterface
{
    public function createQuarters(QuartersModel $quarters): array;

    public function getAllQuarters(): array;

    public function deleteQuarterById(int $id): array;

    public function getAllPhases(): array;

    public function getAllFieldOffices(): array;

    public function getAllSessionActivities(): array;

    public function createSessionActivity(string $name): array;

    public function deleteSessionActivityById(int $id): array;
}