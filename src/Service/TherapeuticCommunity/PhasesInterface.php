<?php

namespace App\Service\TherapeuticCommunity;

interface PhasesInterface
{
    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize): array;
}