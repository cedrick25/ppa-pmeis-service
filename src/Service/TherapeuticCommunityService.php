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
    public const QUARTER_VALIDATION_FAILED = "Quarters validation failed.";
    public const QUARTER_CREATION_FAILED = "Quarter creation failed.";
    public const QUARTER_CREATION_SUCCESS = "Quarter creation successful.";
    public const FETCHING_QUARTER_FAILED = "Fetching quarter failed.";
    public const NO_QUARTER_DATA = "No quarter found.";

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
                return $this->appFormatter->formatResponse(self::QUARTER_VALIDATION_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $quarterId = $this->quartersRepository->create($quarters);

            if ($quarterId == null) {
                return $this->appFormatter->formatResponse(self::QUARTER_CREATION_FAILED, null, ['app' => 'Quarter already exist.']);
            }

            return $this->appFormatter->formatResponse(self::QUARTER_CREATION_SUCCESS, ['id' => $quarterId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::QUARTER_CREATION_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException | \Doctrine\DBAL\Exception\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::QUARTER_CREATION_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAllQuarters(): array
    {
        try {
            $quarters = $this->quartersRepository->list();

            if (sizeof($quarters) == 0) {
                return $this->appFormatter->formatResponse(self::NO_QUARTER_DATA, null);
            }
            return $this->appFormatter->formatResponse(self::QUARTER_CREATION_SUCCESS, ['data' => $quarters]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(self::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}