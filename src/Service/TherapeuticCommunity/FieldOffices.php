<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\FieldOfficesRepository;
use Psr\Cache\InvalidArgumentException;

class FieldOffices implements FieldOfficesInterface
{
    public function __construct(
        private AppFormatter           $appFormatter,
        private FieldOfficesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $fieldOffices = $this->repository->list();

            if (sizeof($fieldOffices) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $fieldOffices);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $fieldOffices = $this->repository->paginated($page, $pageSize);

            if (sizeof($fieldOffices) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $fieldOffices);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}