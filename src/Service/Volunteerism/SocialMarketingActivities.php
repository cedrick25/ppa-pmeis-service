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
        try {
            $activities = $this->repository->findAll();

            if ($activities == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $activities);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}