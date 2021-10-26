<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\TreatmentCategoriesRepository;
use Psr\Cache\InvalidArgumentException;

class TreatmentCategories implements TreatmentCategoriesInterface
{
    public function __construct(
        private AppFormatter                $appFormatter,
        private TreatmentCategoriesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $treatmentCategories = $this->repository->list();

            if (sizeof($treatmentCategories) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SUCCESS, $treatmentCategories);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}