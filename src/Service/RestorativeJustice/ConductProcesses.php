<?php

declare(strict_types=1);

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\RJConductProcesses as ConductProcessesModel;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RjConductedProcessClientsRepository;
use App\Repository\RjConductedProcessPersonsInvolvedRepository;
use App\Repository\RJConductProcessesRepository;
use App\Repository\UserDetailsRepository;
use App\Service\System\AuditTrail;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Exception;

class ConductProcesses implements ConductProcessesInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                          $validator,
        private AppFormatter                                $appFormatter,
        private RJConductProcessesRepository                $repository,
        private RjConductedProcessPersonsInvolvedRepository $conductedProcessPersonsInvolvedRepository,
        private AuditTrail                                  $auditTrail,
        private AppHydrator                                 $hydrator,
        private FieldOfficesRepository                      $fieldOfficesRepository,
        private QuartersRepository                          $quartersRepository,
        private UserDetailsRepository                       $userDetailsRepository,
        private RjConductedProcessClientsRepository         $rjConductedProcessClientsRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(ConductProcessesModel $conductProcessData): array
    {
        try {
            $errors = $this->validator->validate($conductProcessData);

            if ($errors->count() > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($conductProcessData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'RJ conduct process already exist']
                );
            }

            $this->conductedProcessPersonsInvolvedRepository
                ->batchCreate($id, $conductProcessData->getPersonsInvolved());

            $this->rjConductedProcessClientsRepository
                ->bulkCreate($id, $conductProcessData->getClientIds());

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $conductProcessData->jsonSerialize(),
                $this->shortName,
                $id
            );

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $conductProcesses = $this->repository->list();

            if ($conductProcesses == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $return = [];
            $conductProcessesId =  array_map(fn($conductProcess) => $conductProcess->getRJConductProcessId(), $conductProcesses);
            $personsInvolved = $this->conductedProcessPersonsInvolvedRepository->findByConductedProcessIds($conductProcessesId);
            $clients = $this->rjConductedProcessClientsRepository->findClientsWithDetailsByConductProcessId($conductProcessesId);
            $userIds = array_unique(array_map(fn($process) => intval($process->getCreatedBy()), $conductProcesses));
            $createdBys = $this->userDetailsRepository->getCreatedBys($userIds);

            foreach ($conductProcesses as $conductProcess) {
                $conductProcessId = $conductProcess->getRJConductProcessId();
                $arrayVersion = $this->hydrator->convertObjectToArray($conductProcess);
                $arrayVersion['peDate'] = $conductProcess->getPeDate()->format('Y-m-d');
                $arrayVersion['rjpDate'] = $conductProcess->getRjpDate()->format('Y-m-d');

                $arrayVersion['personsInvolved'] = $personsInvolved[$conductProcessId] ?? [];
                $arrayVersion['clients'] = $clients[$conductProcessId] ?? [];
                $arrayVersion['createdBy'] = $createdBys[$conductProcess->getCreatedBy()];

                $return[] = $arrayVersion;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $return);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $conductProcess = $this->repository->isExistingById($id);

        if (!$conductProcess) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $quarter = $this->quartersRepository->find($conductProcess->getQuarterId());
        $region = $this->fieldOfficesRepository->getRegionByFieldOfficeId($conductProcess->getFieldOfficeId());
        $personInvolved = $this->conductedProcessPersonsInvolvedRepository->findByConductedProcessId($conductProcess->getRJConductProcessId());
        $clients = $this->rjConductedProcessClientsRepository->findClientsWithDetailsByConductProcessId([$id]);
        $createdBys = $this->userDetailsRepository->getCreatedBys([$conductProcess->getCreatedBy()]);

        $arrayVersion = $this->hydrator->convertObjectToArray($conductProcess);
        $arrayVersion['peDate'] = $conductProcess->getPeDate()->format('Y-m-d');
        $arrayVersion['rjpDate'] = $conductProcess->getRjpDate()->format('Y-m-d');
        $arrayVersion['regionName'] = $region['region_name'];
        $arrayVersion['regionId'] = $region['region_id'];
        $arrayVersion['year'] = $quarter->getYear();
        $arrayVersion['personsInvolved'] = $personInvolved;
        $arrayVersion['clients'] = $clients[$id];
        $arrayVersion['createdBy'] = $createdBys[$conductProcess->getCreatedBy()];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $arrayVersion);
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

            $this->conductedProcessPersonsInvolvedRepository->deleteByConductedProcessId($id);

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Doctrine\ORM\ORMException|ORMException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getRJIB1(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $conductProcesses = $this->repository->getRJIB1Data($quarterId, $fieldOfficeId);

            if ($conductProcesses == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $return = [];
            $conductProcessesId =  array_map(
                fn($conductProcess) => $conductProcess['rj_conduct_process_id'],
                $conductProcesses
            );
            $personsInvolved = $this->conductedProcessPersonsInvolvedRepository
                ->findByConductedProcessIds($conductProcessesId);

            foreach ($conductProcesses as $conductProcess) {
                $conductProcess['personsInvolved'] = $personsInvolved[$conductProcess['rj_conduct_process_id']];

                $return[] = $conductProcess;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $return);
        } catch (InvalidArgumentException | CacheException  $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
    }

    public function update(int $id, ConductProcessesModel $conductProcessData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $conductProcessData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $conductProcessData->jsonSerialize(), $this->shortName, $id);

            $this->conductedProcessPersonsInvolvedRepository->deleteByConductedProcessId($id);
            $this->conductedProcessPersonsInvolvedRepository->batchCreate($id, $conductProcessData->getPersonsInvolved());

            $this->rjConductedProcessClientsRepository->deleteByConductedProcessId($id);
            $this->rjConductedProcessClientsRepository
                ->bulkCreate($id, $conductProcessData->getClientIds());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['error' => $exception->getMessage()]);
        }
    }
}