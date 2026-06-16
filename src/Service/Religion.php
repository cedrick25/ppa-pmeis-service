<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\ReligionRepository;

class Religion implements ReligionInterface
{
    public function __construct(
        private AppFormatter         $appFormatter,
        private ReligionRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $religions = $this->repository->findAll();

            if (sizeof($religions) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $religions);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}