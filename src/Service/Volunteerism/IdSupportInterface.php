<?php

namespace App\Service\Volunteerism;

use App\Model\IdSupport as IdSupportModel;

interface IdSupportInterface
{
    public function create(IdSupportModel $idSupportData): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getIdSupportReport(int $quarterId, int $fieldOfficeId): array;
}