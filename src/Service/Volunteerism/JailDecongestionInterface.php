<?php

namespace App\Service\Volunteerism;

use App\Model\JailDecongestion as JailDecongestionModel;

interface JailDecongestionInterface
{
    public function create(JailDecongestionModel $data): array;

    public function update(int $id, JailDecongestionModel $data): array;

    public function getAll(): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}