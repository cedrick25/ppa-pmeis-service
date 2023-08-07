<?php

namespace App\Service\RestorativeJustice;

use App\Model\RJConductProcesses as RJConductProcessesModel;

interface ConductProcessesInterface
{
    public function create(RJConductProcessesModel $conductProcessData): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getRJIB1(int $quarterId, int $fieldOfficeId): array;

    public function update(int $id, RJConductProcessesModel $conductProcessData): array;

    public function getPaginated(int $page, int $pageSize, int $filedOfficeId): array;
}