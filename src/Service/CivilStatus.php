<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\CivilStatusRepository;

class CivilStatus implements CivilStatusInterface
{
    public function __construct(
        private AppFormatter          $appFormatter,
        private CivilStatusRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $civilStatuses = $this->repository->findAll();

            if (sizeof($civilStatuses) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $civilStatuses);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}