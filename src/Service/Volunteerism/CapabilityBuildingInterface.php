<?php

namespace App\Service\Volunteerism;

use App\Model\CapabilityBuilding as CapabilityBuildingModel;

interface CapabilityBuildingInterface
{
    public function create(CapabilityBuildingModel $data): array;

    public function getAll(): array;

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array;
}