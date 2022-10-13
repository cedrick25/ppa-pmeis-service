<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\RjRelatedRestitutions as RjRelatedRestitutionsModel;
use App\Repository\RjRelatedRestitutionsRepository;
use App\Service\System\AuditTrail;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RelatedRestitutions implements RelatedRestitutionsInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private RjRelatedRestitutionsRepository $repository,
        private AuditTrail                      $auditTrail,
    ){
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(RjRelatedRestitutionsModel $restitutions): array
    {
        try {
            $errors = $this->validator->validate($restitutions);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($restitutions);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'RJ related restitution already exist']);
            }

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $restitutions->jsonSerialize(),
                $this->shortName,
                $id
            );

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
            $relatedRestitutions = $this->repository->list();

            if ($relatedRestitutions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $relatedRestitutions);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $relatedRestitution = $this->repository->isExistingById($id);

        if (!$relatedRestitution) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $relatedRestitution);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException | ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getRJIB3Data(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $relatedRestitutions = $this->repository->getRJIB3Data($quarterId, $fieldOfficeId);

            if ($relatedRestitutions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $relatedRestitutions);
        } catch (InvalidArgumentException | CacheException  $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function loadForm(int $fieldOfficeId, int $clientId): array
    {
        $recentData = $this->repository->findOneBy(
            ['fieldOfficeId' => $fieldOfficeId, 'clientId' => $clientId,],
            ['rjRelatedRestitutionId' => 'DESC']
        );

        return (null == $recentData) ?
            $this->createLoadFormReturn() :
            $this->createLoadFormReturn($recentData->getOriginalAmount(), $recentData->getBalance(), true);
    }

    private function createLoadFormReturn(
        float $originalAmount = 0,
        float $startOfQuarter = 0,
        bool $isOriginalAmountDisabled = false
    ): array {
        return [
            'originalAmount' => $originalAmount,
            'startOfQuarter' => $startOfQuarter,
            'isOriginalAmountDisabled' => $isOriginalAmountDisabled,
        ];
    }
}