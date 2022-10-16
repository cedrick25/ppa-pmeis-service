<?php

namespace App\Service;

use App\Repository\FieldOfficesRepository;

class FieldOfficeService
{
    public function __construct(
        private FieldOfficesRepository $repository,
    ) {
    }

    /**
     * @param int[] $ids
     * @return int[][]
     */
    public function getRegionIdsByFieldOfficeIds(array $ids): array
    {
        return $this->repository->getRegionIdsByFieldOfficeIds($ids);
    }
}