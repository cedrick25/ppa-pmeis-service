<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Model\Quarters as QuartersModel;
use App\Repository\QuartersRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Quarters implements QuartersInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private AppFormatter       $appFormatter,
        private QuartersRepository $repository,
    ){}
    public function create(QuartersModel $quarters): array
    {
        try {
            $errors = $this->validator->validate($quarters);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_QUARTER_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $quarterId = $this->repository->create($quarters);

            if ($quarterId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['app' => 'Quarter already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_SUCCESS, ['id' => $quarterId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException | \Doctrine\DBAL\Exception\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $quarters = $this->repository->list();

            if (sizeof($quarters) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_QUARTER_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_SUCCESS, $quarters);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isQuarterDeleted = $this->repository->delete($id);

            if (! $isQuarterDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_FAILED, null, ['app' => TCEnum::NO_QUARTER_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}