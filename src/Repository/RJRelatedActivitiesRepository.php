<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\RJRelatedActivities;
use App\Enum\Response as ResponseEnum;
use App\Model\RJRelatedActivities as RJRelatedActivitiesModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method RJRelatedActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJRelatedActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJRelatedActivities[]    findAll()
 * @method RJRelatedActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJRelatedActivitiesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_related_activities";

    public function __construct(
        ManagerRegistry                 $registry,
        private TagAwareCacheInterface  $cache,
        private CacheHelper             $cacheHelper,
        private Helper                  $helper,
        private AppDateHelper           $appDateHelper,
        private RjRelatedActivitiesPersonsInvolvedRepository  $relatedActivitiesPersonsInvolvedRepository,
    ){
        parent::__construct($registry, RJRelatedActivities::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJRelatedActivitiesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rjra.*, c.first_name, c.middle_name, c.last_name, o.name as offense FROM rjrelated_activities as rjra 
                    LEFT JOIN clients as c ON rjra.client_id = c.client_id
                    LEFT JOIN offenses o on rjra.offense_id = o.offenses_id";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @param RJRelatedActivitiesModel $data
     * @return int|null
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function create(RJRelatedActivitiesModel $data): int | null
    {
        if ($this->isExisting($data)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newRjRelatedActivity = new RJRelatedActivities();
        $newRjRelatedActivity->setQuarterId($data->getQuarterId());
        $newRjRelatedActivity->setFieldOfficeId($data->getFieldOfficeId());
        $newRjRelatedActivity->setClientId($data->getClientId());
        $newRjRelatedActivity->setOffenseId($data->getOffenseId());
        $newRjRelatedActivity->setVenueDate($this->appDateHelper->convertStringToImmutableDate($data->getVenueDate()));
        $newRjRelatedActivity->setVenueId($data->getVenueId());
        $newRjRelatedActivity->setVictims($data->getVictims());
        $newRjRelatedActivity->setRjpId($data->getRjpId());
        $newRjRelatedActivity->setRjoId($data->getRjoId());
        $newRjRelatedActivity->setRjGroup($data->getRjGroup());
        $newRjRelatedActivity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newRjRelatedActivity);
        $this->getEntityManager()->flush();

        $id = $newRjRelatedActivity->getRjRelatedActivityId();
        $this->relatedActivitiesPersonsInvolvedRepository->batchCreate($id, $data->getPersonsInvolved());

        return $id;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $RJRelatedActivities = $this->isExistingById($id);
        if (! $RJRelatedActivities) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($RJRelatedActivities);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $RJRelatedActivity =$this->isExistingById($id);

        if ($RJRelatedActivity == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $RJRelatedActivity->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | RJRelatedActivities
    {
        $RJRelatedActivities = $this->findOneBy([
            'rjRelatedActivityId' => $id,
            'deletedAt' => null
        ]);

        return ($RJRelatedActivities == null) ? false : $RJRelatedActivities;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getRJIB2Data(int $quarterId, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getRJIB2Key($quarterId, $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use($quarterId, $fieldOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rjra.*, c.first_name, c.middle_name, c.last_name, c.gender, c.is_pwd, c.is_senior_citizen ,o.name as offense, rjp.name as rj_process, v.name as venue, rjo.name as outcome
                    FROM rjrelated_activities as rjra
                LEFT JOIN clients as c ON rjra.client_id = c.client_id
                LEFT JOIN offenses as o ON rjra.offense_id = o.offenses_id
                LEFT JOIN rjprocesses as rjp ON rjra.rjp_id = rjp.id_rjprocesses
                LEFT JOIN rjoutcomes as rjo ON rjra.rjo_id = rjo.rj_outcome_id
                LEFT JOIN venues as v ON rjra.venue_id = v.venue_id
                WHERE rjra.quarter_id = $quarterId AND rjra.field_office_id = $fieldOfficeId
                AND rjra.deleted_at IS NULL ORDER BY rjra.rj_group";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $data = [];
            $results = $query->fetchAllAssociative();
            foreach ($results as $result) {
                $result['persons_involved'] = $this->relatedActivitiesPersonsInvolvedRepository->getVolunteersByRelatedActivityId((int) $result['rj_related_activity_id']);
                $data[] = $result;
            }

            return $data;
        });
    }

    public function getVolunteerIdsByQuarterAndFieldOffice(int $quarterId, int $fieldOfficeId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rrapi.persons_involved_id FROM rjrelated_activities as rra
                LEFT JOIN rj_related_activities_persons_involved as rrapi
                    ON rra.rj_related_activity_id = rrapi.related_activity_id
                WHERE rra.quarter_id = :quarter_id AND rra.field_office_id = :field_office_id
                  AND rrapi.type = 'VPA'
                  AND rra.deleted_at IS NULL";
        $query = $conn->executeQuery(
            $sql,
            ['quarter_id' => $quarterId, 'field_office_id' => $fieldOfficeId]
        );

        return $query->fetchAllAssociative();
    }

    public function update(int $id, RJRelatedActivitiesModel $data): string
    {
        $entity = $this->find($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setQuarterId($data->getQuarterId());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setClientId($data->getClientId());
        $entity->setOffenseId($data->getOffenseId());
        $entity->setVenueDate($this->appDateHelper->convertStringToImmutableDate($data->getVenueDate()));
        $entity->setVenueId($data->getVenueId());
        $entity->setVictims($data->getVictims());
        $entity->setRjpId($data->getRjpId());
        $entity->setRjoId($data->getRjoId());
        $entity->setRjGroup($data->getRjGroup());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    private function isExisting(RJRelatedActivitiesModel $data): bool | RJRelatedActivities
    {
        $RJRelatedActivities = $this->findOneBy([
            'clientId' => $data->getClientId(),
            'quarterId' => $data->getQuarterId(),
            'fieldOfficeId' => $data->getFieldOfficeId(),
            'deletedAt' => null
        ]);

        return ($RJRelatedActivities == null) ? false : $RJRelatedActivities;
    }
}
