<?php

namespace App\Service;

interface PositionInterface
{
    public function getAll(): array;

    public function create(string $name): array;
}