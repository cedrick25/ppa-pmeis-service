<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\OccupationRepository;

class Occupation implements OccupationInterface
{
    public function __construct(
        private AppFormatter         $appFormatter,
        private OccupationRepository $repository,
    ) {}

    public function getAll(): array
    {
        try {
            $occupations = $this->repository->findAll();

            if (sizeof($occupations) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $occupations);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}
