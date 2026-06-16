<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\ServicesRenderedRepository;

class ServicesRendered implements ServicesRenderedInterface
{
    public function __construct(
        private AppFormatter               $appFormatter,
        private ServicesRenderedRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $servicesRendered = $this->repository->findAll();

            if ($servicesRendered == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $servicesRendered);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['app' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $serviceRendered = $this->repository->find($id);

        if (!$serviceRendered) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $serviceRendered);
    }

}