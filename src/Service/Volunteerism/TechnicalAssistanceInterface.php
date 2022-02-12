<?php

namespace App\Service\Volunteerism;

use App\Model\TechnicalAssistance as TechnicalAssistanceModel;

interface TechnicalAssistanceInterface
{
    public function create(TechnicalAssistanceModel $technicalAssistanceData): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}