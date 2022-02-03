<?php

declare(strict_types=1);

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Repository\QuartersRepository;
use App\Repository\VolunteerOperationsRepository;
use App\Repository\VolunteerRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Operations implements OperationsInterface
{
    public function __construct(
        private ValidatorInterface            $validator,
        private AppFormatter                  $appFormatter,
        private VolunteerOperationsRepository $repository,
        private QuartersRepository            $quartersRepository,
        private AppDateHelper                 $appDateHelper,
        private VolunteerRepository           $volunteerRepository,
    ){}

    public function create(VolunteerOperationsModel $operation): array
    {
        try {
            $errors = $this->validator->validate($operation);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($operation);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Volunteer operations already exist']);
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
            $operations = $this->repository->list();

            if ($operations == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $operations);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $operation = $this->repository->isExistingById($id);

        if (!$operation) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $operation);
    }

    public function getByFieldOfficeAndMonthRange(int $fieldOfficeId, int $quarterId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            $months = $this->appDateHelper->getMonthsByQuarterString($quarter->getName());

            $inactiveVolunteers = $this->volunteerRepository->findInactiveVolunteersByFieldOfficeAndMonthRange(
                $fieldOfficeId, $quarterId, intval($quarter->getYear()), $months
            );

            // get the latest status cross-check to volunteer list

            $appointedVolunteers = $this->repository->findByFieldOfficeAndMonthRange(
                $fieldOfficeId,
                intval($quarter->getYear()),
                $months,
                'APPOINTED'
            );
            $reappointedVolunteers = $this->repository->findByFieldOfficeAndMonthRange(
                $fieldOfficeId,
                intval($quarter->getYear()),
                $months,
                'REAPPOINTED'
            );
            $droppedVolunteers = $this->repository->findByFieldOfficeAndMonthRange(
                $fieldOfficeId,
                intval($quarter->getYear()),
                $months,
                'DROPPED'
            );

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, [
                'APPOINTED' => $appointedVolunteers,
                'REAPPOINTED' => $reappointedVolunteers,
                'INACTIVE' => $inactiveVolunteers,
                'DROPPED' => $droppedVolunteers
            ]);
        } catch (\Exception | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }
}