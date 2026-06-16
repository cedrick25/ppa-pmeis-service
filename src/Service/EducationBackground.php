<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\EducationBackgroundRepository;

class EducationBackground implements EducationBackgroundInterface
{
    public function __construct(
        private AppFormatter                  $appFormatter,
        private EducationBackgroundRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $educationBackgrounds = $this->repository->findAll();

            if (sizeof($educationBackgrounds) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $educationBackgrounds);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}