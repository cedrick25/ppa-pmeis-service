<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Repository\CapabilityBuildingRepository;
use App\Repository\QuartersRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class CapabilityBuilding implements CapabilityBuildingInterface
{
    private string $shortName;

    public function __construct(
        private AppFormatter                 $appFormatter,
        private CapabilityBuildingRepository $repository,
        private QuartersRepository           $quartersRepository,
        private AuditTrail                   $auditTrail,
        private AppHydrator                  $hydrator,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): array
    {
        try {
            $this->repository->batchCreate($data);

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $this->hydrator->convertObjectToArray($data),
                $this->shortName,
            );

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, []);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $capabilityBuildings = $this->repository->list();

            if ($capabilityBuildings == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $capabilityBuildings);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array
    {
        try {
            $data = [];
            $quarter = $this->quartersRepository->find($quarterId);

            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $capabilityBuildings = $this->repository->findReport($minMaxDate, $fieldOfficeId, $type);

            foreach ($capabilityBuildings as $capabilityBuilding) {
                if (! isset($data[$capabilityBuilding['subtype']])) {
                    $data[$capabilityBuilding['subtype']] = [];
                }

                $data[$capabilityBuilding['subtype']][] = $capabilityBuilding;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['orm' => $e->getMessage()]);
        }
    }
}