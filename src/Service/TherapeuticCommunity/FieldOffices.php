<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\FieldOfficesRepository;
use Psr\Cache\InvalidArgumentException;

class FieldOffices implements FieldOfficesInterface
{
    public function __construct(
        private AppFormatter           $appFormatter,
        private FieldOfficesRepository $fieldOfficesRepository,
    ){}

    public function getAll(): array
    {
        try {
            $fieldOffices = $this->fieldOfficesRepository->list();

            if (sizeof($fieldOffices) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_FIELD_OFFICE_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FIELD_OFFICE_SUCCESS, $fieldOffices);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FIELD_OFFICE_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}