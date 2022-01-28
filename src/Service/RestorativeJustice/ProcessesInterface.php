<?php

namespace App\Service\RestorativeJustice;

interface ProcessesInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}