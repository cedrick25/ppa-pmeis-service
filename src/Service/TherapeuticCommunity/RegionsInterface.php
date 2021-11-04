<?php

namespace App\Service\TherapeuticCommunity;

interface RegionsInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}