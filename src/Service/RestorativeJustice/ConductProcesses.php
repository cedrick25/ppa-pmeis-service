<?php

declare(strict_types=1);

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\RJConductProcesses as ConductProcessesModel;
use App\Repository\RJConductProcessesRepository;
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
        private ValidatorInterface           $validator,
        private AppFormatter                 $appFormatter,
        private RJConductProcessesRepository $repository,
        private AuditTrail                   $auditTrail,
        private AppHydrator                  $hydrator,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(ConductProcessesModel $conductProcessData): array
    {
        try {
            $errors = $this->validator->validate($conductProcessData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($conductProcessData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'RJ conduct process already exist']);
            }

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $this->hydrator->convertObjectToArray($conductProcessData),
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

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $conductProcesses);
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

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $conductProcess);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException | ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getRJIB1(int $quarterId, int $fieldOfficeId): array
    {
        // TODO: replaced stakeholders to associates data
        try {
            $conductProcesses = $this->repository->getRJIB1Data($quarterId, $fieldOfficeId);

            if ($conductProcesses == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $conductProcesses);
        } catch (InvalidArgumentException | CacheException  $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }
}