<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\Quarters as QuartersModel;
use App\Repository\ClientSessionsRepository;
use App\Repository\QuartersRepository;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Quarters implements QuartersInterface
{
    public function __construct(
        private ValidatorInterface       $validator,
        private AppFormatter             $appFormatter,
        private QuartersRepository       $repository,
        private ClientSessionsRepository $clientSessionsRepository,
    ){}

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

    public function getTCA1Part1(int $id, int $fieldOfficeId): array
    {
        try {
            $sessions = $this->repository->fetchTCA1Part1($id, $fieldOfficeId);

            if (null === $sessions) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $sessionsIds = array_map(fn(array $session) => intval($session['session_id']), $sessions);
            $fsgNumbers = $this->getFsgNumbersBySessionId($sessionsIds);

            $sessions = array_map(function(array $session) use($fsgNumbers) {
                $session['fsg'] = $fsgNumbers[$session['session_id']];

                return $session;
            }, $sessions);

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getTCA1Part2(int $id, int $fieldOfficeId): array
    {
        try {
            $quarters = $this->repository->fetchTCA1Part2($id, $fieldOfficeId);

            if (!$quarters) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $quarters);
        } catch (CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $e->getMessage()]);
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

    /**
     * @param int[] $sessionIds
     * @return array<string, int>
     */
    private function getFsgNumbersBySessionId(array $sessionIds): array
    {
        /**
         * Criteria:
         *  Per Session ID
         *  Same session, client
         *  TODO: Verify the scenario where there is 2 fsi of the same client in the same session
         *  For now every fsi is counted as 1 regardless of
         */
        $data = [];
        $clientsSessionSessionIds = $this->clientSessionsRepository->getFsiBySessionIds($sessionIds);

        foreach ($clientsSessionSessionIds as $clientsSessionSessionId) {
            if (! isset($data[$clientsSessionSessionId['session_id']])) {
                $data[$clientsSessionSessionId['session_id']] = 0;
            }

            $data[$clientsSessionSessionId['session_id']]++;
        }

        return $data;
    }
}