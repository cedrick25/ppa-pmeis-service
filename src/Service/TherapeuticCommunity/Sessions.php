<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Model\Sessions as SessionsModel;
use App\Repository\SessionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
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
}