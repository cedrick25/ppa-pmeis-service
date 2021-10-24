<?php

namespace App\Service\TherapeuticCommunity;

interface VenuesInterface
{
    public function create(string $name):array;

    public function getAll(): array;

    public function deleteById(int $id): array;
}