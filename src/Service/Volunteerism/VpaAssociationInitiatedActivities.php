<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;
use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VpaAssociationInitiatedActivities implements VpaAssociationInitiatedActivitiesInterface
{
    public function __construct(
        private ValidatorInterface                          $validator,
        private AppFormatter                                $appFormatter,
        private VpaAssociationInitiatedActivitiesRepository $repository,
    ){}

    public function create(VpaAssociationInitiatedActivitiesModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($data);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Vpa association initiated activities already exist']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $vpaAssociationInitiatedActivity = $this->repository->isExistingById($id);

        if (!$vpaAssociationInitiatedActivity) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $vpaAssociationInitiatedActivity);
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
        } catch (\Doctrine\ORM\ORMException | ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $vpaAssociationInitiatedActivities = $this->repository->getReport($quarterId, $fieldOfficeId);

            if ($vpaAssociationInitiatedActivities == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $vpaAssociationInitiatedActivities);
        } catch (InvalidArgumentException | CacheException  $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        } catch (\Doctrine\DBAL\Exception | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }
}