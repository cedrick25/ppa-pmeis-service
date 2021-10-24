<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Model\Sessions as SessionsModel;
use App\Repository\SessionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Sessions implements SessionsInterface
{
    public function __construct(
        private AppFormatter       $appFormatter,
        private SessionsRepository $repository,
        private ValidatorInterface $validator,
    ){}

    public function create(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_SESSION_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_FAILED, null, ['app' => 'Session activity already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $sessions = $this->repository->list();

            if (sizeof($sessions) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_SESSIONS_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSIONS_SUCCESS, $sessions);
        } catch (\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSIONS_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_FAILED, null, ['app' => TCEnum::NO_SESSIONS_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_SUCCESS, null);
        } catch (\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, SessionsModel $sessionData):array
    {
        try {
            $isUpdated = $this->repository->update($id, $sessionData);

            if (! $isUpdated) {
                return $this->appFormatter->formatResponse(TCEnum::UPDATING_SESSION_FAILED, null, ['app' => 'Session record not found.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::UPDATING_SESSION_SUCCESS, null);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::UPDATING_SESSION_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (InvalidArgumentException | Exception $e) {
            return $this->appFormatter->formatResponse(TCEnum::UPDATING_SESSION_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(TCEnum::UPDATING_SESSION_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }
}