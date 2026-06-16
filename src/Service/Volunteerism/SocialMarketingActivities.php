<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\SocialMarketingActivitiesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class SocialMarketingActivities implements SocialMarketingActivitiesInterface
{
    public function __construct(
        private AppFormatter                         $appFormatter,
        private SocialMarketingActivitiesRepository  $repository,
    ){}

    public function getAll(): array
    {
        $activities = $this->repository->findAll();

        if ($activities == null) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $activities);
    }
}