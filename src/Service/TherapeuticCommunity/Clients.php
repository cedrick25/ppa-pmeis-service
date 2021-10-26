<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Model\Clients as ClientModel;
use App\Repository\ClientsRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Clients implements ClientsInterface
{
    public function __construct(
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
        private ClientsRepository $repository,
    ){}

    public function create(ClientModel $clientData): array
    {
        try {
            $errors = $this->validator->validate($clientData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($clientData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => 'Client already exist']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $clients = $this->repository->list();

            if (sizeof($clients) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SUCCESS, $clients);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        // TODO: Implement deleteById() method.
        return [];
    }

    public function updateById(int $id, ClientModel $clientData): array
    {
        // TODO: Implement updateById() method.
        return [];
    }
}