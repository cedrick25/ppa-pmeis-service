<?php

namespace App\Service\TherapeuticCommunity;

interface PhasesInterface
{
    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;
}