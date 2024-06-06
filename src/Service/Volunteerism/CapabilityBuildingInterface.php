<?php

namespace App\Service\Volunteerism;

interface CapabilityBuildingInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): array;

    public function getAll(): array;

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array;

    public function deleteById(int $id): array;

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array;

    public function update(int $id, array $data): array;

    public function getById(int $id, string $type): array;
}
