<?php

namespace App\Service\Volunteerism;

use App\Model\ProgramMaterialsDevelopment as ProgramMaterialsDevelopmentModel;

interface ProgramMaterialsDevelopmentInterface
{
    public function create(ProgramMaterialsDevelopmentModel $data): array;

    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getIdSupportReport(int $quarterId, int $fieldOfficeId): array;

    public function update(int $id, ProgramMaterialsDevelopmentModel $data): array;
}