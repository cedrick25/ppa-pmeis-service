<?php

namespace App\Service\TherapeuticCommunity;

interface SessionActivitiesInterface
{
    public function getAllSessionActivities(): array;

    public function createSessionActivity(string $name): array;

    public function deleteSessionActivityById(int $id): array;
}