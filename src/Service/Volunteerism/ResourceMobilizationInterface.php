<?php

namespace App\Service\Volunteerism;

use App\Model\ResourceMobilization as ResourceMobilizationModel;

interface ResourceMobilizationInterface
{
    public function create(ResourceMobilizationModel $data): array;

    public function update(int $id, ResourceMobilizationModel $data): array;

    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}