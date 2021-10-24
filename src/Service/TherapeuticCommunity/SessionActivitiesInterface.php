<?php

namespace App\Service\TherapeuticCommunity;

interface SessionActivitiesInterface
{
    public function getAll(): array;

    public function create(string $name): array;

    public function deleteById(int $id): array;
}