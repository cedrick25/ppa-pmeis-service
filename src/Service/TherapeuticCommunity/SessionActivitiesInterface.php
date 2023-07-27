<?php

namespace App\Service\TherapeuticCommunity;

interface SessionActivitiesInterface
{
    public function getAll(): array;

    public function create(
        string $name,
        ?int $phaseId,
        int $treatmentCategoryId,
    ): array;

    public function deleteById(int $id): array;

    public function updateById(
        int $id,
        string $name,
        ?int $phaseId,
        int $treatmentCategoryId,
    ): array;

    public function getById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;
}