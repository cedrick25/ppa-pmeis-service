<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\ClientTypesRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use App\Model\ClientTypes as ClientTypesModel;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientTypes implements ClientTypesInterface
{
    public function __construct(
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
        private ClientTypesRepository $repository,
    ){}

    public function create(ClientTypesModel $clientTypes): array
    {
        try {
            $errors = $this->validator->validate($clientTypes);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($clientTypes->getCode(), $clientTypes->getDescription());

            if ($id == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => 'Type already exist']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $clientTypes = $this->repository->list();

            if (sizeof($clientTypes) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SUCCESS, $clientTypes);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}