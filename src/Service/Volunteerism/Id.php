<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\VolunteerId as VolunteerIdModel;
use App\Repository\VolunteerIdRepository;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Id implements IdInterface
{
    public function __construct(
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
        private VolunteerIdRepository $repository,
    ){}

    public function create(VolunteerIdModel $idData): array
    {
        try {
            $errors = $this->validator->validate($idData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($idData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Volunteer id already exist']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $ids = $this->repository->list();

            if ($ids == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $ids);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(string $idData): array
    {
        $id = $this->repository->isExistingById($idData);

        if (!$id) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $id);
    }
}