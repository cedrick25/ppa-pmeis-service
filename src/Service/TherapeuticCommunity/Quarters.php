<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Entity\Quarters as EntityQuarters;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\Quarters as QuartersModel;
use App\Repository\QuartersRepository;
use App\Service\System\AuditTrail;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Quarters implements QuartersInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface       $validator,
        private AppFormatter             $appFormatter,
        private QuartersRepository       $repository,
        private AuditTrail               $auditTrail,
    ){
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(QuartersModel $quarters): array
    {
        try {
            $errors = $this->validator->validate($quarters);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $quarterId = $this->repository->create($quarters);

            if ($quarterId == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Quarter already exist.']);
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $quarters->jsonSerialize(), $this->shortName, $quarterId);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $quarterId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException | \Doctrine\DBAL\Exception\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $quarters = $this->repository->list();

            if (sizeof($quarters) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $quarters);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, QuartersModel $quarterData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $quarterData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $quarterData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $phases = $this->repository->paginated($page, $pageSize);

            if (sizeof($phases) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $phases);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $quarter = $this->repository->isExistingById($id);

        if (!$quarter) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $quarter);
    }

    public function searchPaginated(string $field, string $query,int $page, int $pageSize): array
    {
        try {
            $phases = $this->repository->paginatedSearch($field, $query, $page, $pageSize);

            if (sizeof($phases) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $phases);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getByYear(string $year): array
    {
        $quarters = $this->repository->findBy([
            'year' => $year
        ]);;

        if (!$quarters) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $quarters);
    }

    public function getQuarterData(int $id): EntityQuarters
    {
        return $this->repository->find($id);
    }
}