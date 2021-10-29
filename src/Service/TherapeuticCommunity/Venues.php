<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\VenuesRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;

class Venues implements VenuesInterface
{
    public function __construct(
        private AppFormatter     $appFormatter,
        private VenuesRepository $repository,
    ){}

    public function create(string $name):array
    {
        try {
            if ($name === "") {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_FAILED, null, ['app' => 'Venue name cannot be empty.']);
            }

            $venueId = $this->repository->create($name);

            if ($venueId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => 'Venue already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SUCCESS, ['id' => $venueId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $venues = $this->repository->list();

            if (sizeof($venues) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SUCCESS, $venues);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['app' => TCEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}