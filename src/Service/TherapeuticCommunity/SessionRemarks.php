<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\SessionRemarksRepository;

class SessionRemarks implements SessionRemarksInterface
{
    public function __construct(
        private AppFormatter             $appFormatter,
        private SessionRemarksRepository $repository,
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