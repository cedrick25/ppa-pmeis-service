<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\ProgramMaterialsDevelopment as ProgramMaterialsDevelopmentModel;
use App\Repository\PmdPersonResponsibleRepository;
use App\Repository\ProgramMaterialsDevelopmentRepository;
use App\Repository\QuartersRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProgramMaterialsDevelopment implements ProgramMaterialsDevelopmentInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                      $validator,
        private AppFormatter                            $appFormatter,
        private ProgramMaterialsDevelopmentRepository   $repository,
        private QuartersRepository                      $quartersRepository,
        private AuditTrail                              $auditTrail,
        private PmdPersonResponsibleRepository          $pmdPersonResponsibleRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(ProgramMaterialsDevelopmentModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if (count($errors) > 0) {
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
                    ['app' => 'Program Materials Development already exist']
                );
            }

            $this->pmdPersonResponsibleRepository->batchCreate($id, $data->getPersonsResponsible());

            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (\Exception | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $programMaterialsDevelopments = $this->repository->list();

            if ($programMaterialsDevelopments == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $programMaterialsDevelopments);
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

            $pmdIds = array_map(
                fn($item) => (int) $item['program_materials_development_id'],
                $results['items']
            );
            $personsResponsible = $this->pmdPersonResponsibleRepository
                ->findPersonsResponsibleByPmdId($pmdIds);

            foreach ($results['items'] as $i => $item) {
                $pmdId = $item['program_materials_development_id'];
                $results['items'][$i]['utilized_for'] = [
                    'label' => $item['utilized_for'],
                    'value' => $item['utilized_for'],
                ];
                $results['items'][$i]['personsResponsible'] = $personsResponsible[$pmdId];
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

    public function getById(int $id): array
    {
        $result = $this->repository->getById($id);

        if (!$result) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $result['utilized_for'] = [
            'label' => $result['utilized_for'],
            'value' => $result['utilized_for'],
        ];
        $result['personsResponsible'] = $this->pmdPersonResponsibleRepository
            ->findPersonsResponsibleByPmdId([$id])[$id];

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

            $this->pmdPersonResponsibleRepository->deleteByPmdId($id);
            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException | \Doctrine\ORM\ORMException | ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getIdSupportReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $results = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);

            $pmdIds = array_map(
                fn($item) => (int) $item['program_materials_development_id'],
                $results
            );
            $personsResponsible = $this->pmdPersonResponsibleRepository
                ->findPersonsResponsibleByPmdId($pmdIds);

            foreach ($results as $i => $item) {
                $pmdId = $item['program_materials_development_id'];
                $results[$i]['personsResponsible'] = $personsResponsible[$pmdId];
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

    public function update(int $id, ProgramMaterialsDevelopmentModel $data): array
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

            $this->pmdPersonResponsibleRepository->deleteByPmdId($id);
            $this->pmdPersonResponsibleRepository
                ->batchCreate($id, $data->getPersonsResponsible());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Exception | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }
}