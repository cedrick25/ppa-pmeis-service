<?php

namespace App\Service\Volunteerism;

use App\Model\ResourceMobilization as ResourceMobilizationModel;

interface ResourceMobilizationInterface
{
    public function create(ResourceMobilizationModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

}