<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\ActiveSupervisionRemarksRepository;
use App\Repository\SessionRemarksRepository;

class ActiveSupervisionRemarks implements ActiveSupervisionRemarksInterface
{
    public function __construct(
        private AppFormatter                       $appFormatter,
        private ActiveSupervisionRemarksRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $remarks = $this->repository->findAll();

            if (sizeof($remarks) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $remarks);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}