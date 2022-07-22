<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;
use App\Repository\ClientSessionsRepository;
use App\Repository\QuartersRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\VolunteerRepository;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResourceFacilitatorSession implements ResourceFacilitatorSessionInterface
{
    public function __construct(
        private ValidatorInterface                   $validator,
        private AppFormatter                         $appFormatter,
        private ResourceFacilitatorSessionRepository $repository,
        private VolunteerRepository                  $volunteerRepository,
        private ClientSessionsRepository             $clientSessionsRepository,
        private QuartersRepository                   $quartersRepository,
    ){}

    public function create(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): array
    {
        try {
            $errors = $this->validator->validate($resourceFacilitatorSessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($resourceFacilitatorSessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Resource facilitator session already exist']);
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
            $resourceFacilitatorSession = $this->repository->list();

            if (sizeof($resourceFacilitatorSession) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $resourceFacilitatorSession);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
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
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $resourceFacilitatorSessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $resourceFacilitatorSession = $this->repository->isExistingById($id);

        if (!$resourceFacilitatorSession) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $resourceFacilitatorSession);
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $resourceFacilitatorSessions = $this->repository->paginated($page, $pageSize);

            if (sizeof($resourceFacilitatorSessions) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $resourceFacilitatorSessions);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getVPA3(int $fieldOfficeId, int $quarterId): array
    {
        try {
            $quarterData = $this->quartersRepository->find($quarterId);

            if ($quarterData === null) {
                return [];
            }

            $activeVolunteers = $this->repository->getVolunteerIdsByQuarterAndFieldOfficeId($fieldOfficeId, $quarterData->getName(), intval($quarterData->getYear()));
            $data = [];

            foreach ($activeVolunteers as $activeVolunteer) {
                $volunteer = $this->volunteerRepository->getById($activeVolunteer['resource_facilitator_id']);
                if (! $volunteer) {
                    continue;
                }

                $data[] = [
                    'volunteer' => [
                        'first_name' => $volunteer['first_name'],
                        'middle_name' => $volunteer['middle_name'],
                        'last_name' => $volunteer['last_name'],
                        'gender' => $volunteer['gender']
                    ],
                    'clients' => $this->clientSessionsRepository->getVPA3Report($activeVolunteer['resource_facilitator_id']),
                ];
            }

            if (sizeof($data) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception|\Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }
}