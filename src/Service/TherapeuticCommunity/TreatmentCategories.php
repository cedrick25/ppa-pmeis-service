<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\TreatmentCategoriesRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;

class TreatmentCategories implements TreatmentCategoriesInterface
{
    public function __construct(
        private AppFormatter                $appFormatter,
        private TreatmentCategoriesRepository $repository
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

    public function create(string $name): array
    {
        try {

            if ($name === "") {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_FAILED, null, ['app' => 'Treatment Category name cannot be empty.']);
            }

            $id = $this->repository->create($name);

            if ($id == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => 'Treatment Category exist']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        return [];
    }
}