<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
use App\Repository\SessionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Sessions implements SessionsInterface
{
    public function __construct(
        private AppFormatter       $appFormatter,
        private SessionsRepository $repository,
        private ValidatorInterface $validator,
        private AppDateHelper $appDateHelper,
    ){}

    public function create(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Session already exist.']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function createWithClientsAndFacilitators(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->createWithClientsAndFacilitators($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Session already exist.']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $sessions = $this->repository->list();

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getAllWithClientsAndFacilitators(): array
    {
        try {
            $sessions = $this->repository->listWithClientsAndFacilitators();

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, SessionsModel $sessionData):array
    {
        try {
            $isUpdated = $this->repository->update($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (InvalidArgumentException | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function updateByIdWithClientAndFacilitators(int $id, SessionsModel $sessionData):array
    {
        try {
            $isUpdated = $this->repository->updateWithClientAndFacilitators($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Doctrine\DBAL\Driver\Exception | ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (InvalidArgumentException | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        try {
            $session = $this->repository->fetchById($id);

            if (!$session) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $session);
        } catch (\Psr\Cache\InvalidArgumentException | CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $sessions = $this->repository->paginated($page, $pageSize);

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getTCIA2(int $quarterId, string $role): array
    {
        try {
            $sessions = $this->repository->fetchTCIA2($quarterId, $role);

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $rows = [];

            foreach ($sessions as $session) {
                $middleInitial = $session['middle_name'] != null ? substr($session['middle_name'], 0, 1) : '';
                $fullName = $session['last_name'] . '_' . $session['first_name'] . '_' . $middleInitial;
                $monthInitial = $this->appDateHelper->getFirstLetterOfMonthFromDateString($session['date']);
                $rowIdentifier = $fullName . '_' . $session['phase'];
                $monthIdentifier = $session['quarter'] . '_' . $monthInitial;

                if (! isset($rows[$rowIdentifier])) {
                    $session[$monthIdentifier] = 1;
                    $rows[$rowIdentifier] = $session;
                } else {
                    $rows[$rowIdentifier][$monthIdentifier] = 1;
                }
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $rows);
        } catch (\Doctrine\DBAL\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $e->getMessage()]);
        }
    }
}