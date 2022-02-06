<?php

namespace App\Service\RestorativeJustice;

use App\Model\RjRelatedRestitutions as RjRelatedRestitutionsModel;

interface RelatedRestitutionsInterface
{
    public function create(RjRelatedRestitutionsModel $restitutions): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getRJIB3Data(int $quarterId, int $fieldOfficeId): array;
}