<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\JailDecongestion as JailDecongestionModel;
use App\Repository\JailDecongestionPersonResponsibleRepository;
use App\Repository\JailDecongestionRepository;
use App\Repository\QuartersRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JailDecongestion implements JailDecongestionInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private JailDecongestionRepository      $repository,
        private QuartersRepository              $quartersRepository,
        private AuditTrail                      $auditTrail,
        private JailDecongestionPersonResponsibleRepository $personResponsibleRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(JailDecongestionModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if ($errors->count() > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($data);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Jail Decongestion already exist']
                );
            }

            $this->personResponsibleRepository->batchCreate($id, $data->getPersonResponsible());
            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function update(int $id, JailDecongestionModel $data): array
    {
        try {
            $isUpdated = $this->repository->update($id, $data);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(
                AuditTrailActions::UPDATE,
                $data->jsonSerialize(),
                $this->shortName,
                $id
            );

            $this->personResponsibleRepository->deleteByJailDecongestionId($id);
            $this->personResponsibleRepository
                ->batchCreate($id, $data->getPersonResponsible());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Exception | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $results = $this->repository->list();

            if ($results == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $results = $this->repository->paginated($page, $pageSize, $fieldOfficeId);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $jdIds = array_map(
                fn($item) => (int) $item['jail_decongestion_id'],
                $results['items']
            );
            $personsResponsible = $this->personResponsibleRepository->findByJailDecongestionId($jdIds);

            foreach ($results['items'] as $i => $item) {
                $jdId = $item['jail_decongestion_id'];
                $results['items'][$i]['personsResponsible'] = $personsResponsible[$jdId];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getById(int $id): array
    {
        $result = $this->repository->getById($id);

        if (! $result) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $result['personResponsible'] = $this->personResponsibleRepository->findByJailDecongestionId([$id])[$id];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $result);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::DELETING_FAILED,
                    null,
                    ['app' => ResponseEnum::NO_DATA]
                );
            }

            $this->personResponsibleRepository->deleteByJailDecongestionId($id);
            $this->auditTrail->log(AuditTrailActions::CREATE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Doctrine\ORM\ORMException | ORMException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $results = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);

            $jdIds = array_map(
                fn($item) => (int) $item['jail_decongestion_id'],
                $results
            );
            $personsResponsible = $this->personResponsibleRepository->findByJailDecongestionId($jdIds);

            foreach ($results as $i => $item) {
                $jdId = $item['jail_decongestion_id'];
                $results[$i]['personsResponsible'] = $personsResponsible[$jdId];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }
}