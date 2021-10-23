<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Model\Quarters as QuartersModel;
use App\Repository\QuartersRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TherapeuticCommunityService implements TherapeuticCommunityServiceInterface
{
    public const VALIDATING_QUARTER_FAILED = "Validation quarter failed.";
    public const CREATING_QUARTER_FAILED = "Creating quarter failed.";
    public const CREATING_QUARTER_SUCCESS = "Creating quarter successful.";
    public const FETCHING_QUARTER_FAILED = "Fetching quarter failed.";
    public const FETCHING_QUARTER_SUCCESS = "Fetching quarter success.";
    public const NO_QUARTER_DATA = "No quarter found.";
    public const DELETING_QUARTER_FAILED = "Deleting quarter failed.";
    public const DELETING_QUARTER_SUCCESS = "Deleting quarter success.";


    public function __construct(
        private QuartersRepository $quartersRepository,
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
    ){}

    public function createQuarters(QuartersModel $quarters): array
    {
        try {
            $errors = $this->validator->validate($quarters);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(self::VALIDATING_QUARTER_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $quarterId = $this->quartersRepository->create($quarters);

            if ($quarterId == null) {
                return $this->appFormatter->formatResponse(self::CREATING_QUARTER_FAILED, null, ['app' => 'Quarter already exist.']);
            }

            return $this->appFormatter->formatResponse(self::CREATING_QUARTER_SUCCESS, ['id' => $quarterId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::CREATING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException | \Doctrine\DBAL\Exception\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::CREATING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAllQuarters(): array
    {
        try {
            $quarters = $this->quartersRepository->list();

            if (sizeof($quarters) == 0) {
                return $this->appFormatter->formatResponse(self::NO_QUARTER_DATA, null);
            }

            return $this->appFormatter->formatResponse(self::FETCHING_QUARTER_SUCCESS, ['data' => $quarters]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteQuarterById(int $id): array
    {
        try {
            $isQuarterDeleted = $this->quartersRepository->delete($id);

            if (! $isQuarterDeleted) {
                return $this->appFormatter->formatResponse(self::DELETING_QUARTER_FAILED, null, ['app' => self::NO_QUARTER_DATA]);
            }

            return $this->appFormatter->formatResponse(self::DELETING_QUARTER_SUCCESS, null);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(self::DELETING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}