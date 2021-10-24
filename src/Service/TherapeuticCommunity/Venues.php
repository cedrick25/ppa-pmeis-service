<?php

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
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_VENUE_FAILED, null, ['app' => 'Venue name cannot be empty.']);
            }

            $venueId = $this->repository->create($name);

            if ($venueId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_VENUE_FAILED, null, ['app' => 'Venue already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_VENUE_SUCCESS, ['id' => $venueId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_VENUE_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_VENUE_FAILED, null, ['orm' => $exception->getMessage()]);
        }
        return [];
    }
}