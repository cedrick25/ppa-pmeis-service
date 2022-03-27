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
}