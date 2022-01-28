<?php

namespace App\Service\RestorativeJustice;

interface ProcessStatusInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}