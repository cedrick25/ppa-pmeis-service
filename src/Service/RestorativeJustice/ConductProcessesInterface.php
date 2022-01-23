<?php

namespace App\Service\RestorativeJustice;

use App\Model\RJConductProcesses as RJConductProcessesModel;

interface ConductProcessesInterface
{
    public function create(RJConductProcessesModel $RJConductProcessData): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getRJIB1(int $clientId, int $quarterId, int $fieldOfficeId): array;
}