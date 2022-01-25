<?php

namespace App\Service\RestorativeJustice;

interface RJProcessesInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}