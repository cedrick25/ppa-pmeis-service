<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\VpaAssociationInitiatedActivities;
use App\Enum\Response as ResponseEnum;
use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method VpaAssociationInitiatedActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpaAssociationInitiatedActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpaAssociationInitiatedActivities[]    findAll()
 * @method VpaAssociationInitiatedActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpaAssociationInitiatedActivitiesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "vpa_association_initiated_activities";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, VpaAssociationInitiatedActivities::class);
    }

    /**
     * @return VpaAssociationInitiatedActivities[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVpaAssociationInitiatedActivities(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('vs')
                ->orderBy('vs.vpaAssociationInitiatedActivityId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(VpaAssociationInitiatedActivitiesModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newVpaAssociationInitiatedActivities = new VpaAssociationInitiatedActivities();
        $newVpaAssociationInitiatedActivities->setServiceRenderedId($data->getServicesRenderedId());
        $newVpaAssociationInitiatedActivities->setVenueDate(
            $this->appDateHelper->convertStringToImmutableDate($data->getVenueDate())
        );
        $newVpaAssociationInitiatedActivities->setVenueId($data->getVenueId());
        $newVpaAssociationInitiatedActivities->setVolunteerId($data->getVolunteerId());
        $newVpaAssociationInitiatedActivities->setRole($data->getRole());
        $newVpaAssociationInitiatedActivities->setCrdResourcesTapped($data->getCrdResourcesTapped());
        $newVpaAssociationInitiatedActivities->setCrdAssistanceReceived($data->getCrdAssistanceReceived());
        $newVpaAssociationInitiatedActivities->setRemarks($data->getRemarks());
        $newVpaAssociationInitiatedActivities->setFieldOfficeId($data->getFieldOfficeId());
        $newVpaAssociationInitiatedActivities->setQuarterId($data->getQuarterId());
        $newVpaAssociationInitiatedActivities->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());
        $newVpaAssociationInitiatedActivities->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newVpaAssociationInitiatedActivities);
        $this->getEntityManager()->flush();

        return $newVpaAssociationInitiatedActivities->getVpaAssociationInitiatedActivityId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $vpaAssociationInitiatedActivities = $this->isExistingById($id);
        if (! $vpaAssociationInitiatedActivities) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($vpaAssociationInitiatedActivities);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | VpaAssociationInitiatedActivities
    {
        $vpaAssociationInitiatedActivities = $this->findOneBy([
            'vpaAssociationInitiatedActivityId' => $id,
            'deletedAt' => null
        ]);

        return ($vpaAssociationInitiatedActivities == null) ? false : $vpaAssociationInitiatedActivities;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT sr.name service_rendered, vaia.venue_date, v.name as venue, v2.first_name,
                v2.middle_name, v2.last_name, v2.gender, vaia.role, vaia.crd_resources_tapped,
                vaia.crd_assistance_received, vaia.remarks
                FROM vpa_association_initiated_activities vaia
                LEFT JOIN services_rendered sr on vaia.service_rendered_id = sr.services_rendered_id
                LEFT JOIN venues v on vaia.venue_id = v.venue_id
                LEFT JOIN volunteer v2 on vaia.volunteer_id = v2.volunteer_id
                WHERE vaia.field_office_id = $fieldOfficeId
                AND vaia.quarter_id = $quarterId
                AND vaia.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getVolunteerIdsByDateRange(int $quarterId, int $fieldOfficeId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
                    DISTINCT vaia.volunteer_id
                    FROM vpa_association_initiated_activities vaia
                WHERE vaia.quarter_id = $quarterId
                AND vaia.field_office_id = $fieldOfficeId
                AND vaia.deleted_at IS NULL
                ";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => 'volunteer_association_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT vaia.*, v.last_name, v.first_name, v.middle_name, fo.name field_office, r.name region,
                        r.region_id as region_id, sr.name service_rendered, ve.name venue
                    FROM vpa_association_initiated_activities as vaia
                    LEFT JOIN volunteer v on vaia.volunteer_id = v.volunteer_id
                    LEFT JOIN services_rendered sr on vaia.service_rendered_id = sr.services_rendered_id
                    LEFT JOIN venues ve on vaia.venue_id = ve.venue_id
                    LEFT JOIN field_offices fo on vaia.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE vaia.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    public function getById(int $id): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT vaia.*, v.last_name, v.first_name, v.middle_name, fo.name field_office, r.name region,
                        r.region_id as region_id, sr.name service_rendered, ve.name venue
                    FROM vpa_association_initiated_activities as vaia
                    LEFT JOIN volunteer v on vaia.volunteer_id = v.volunteer_id
                    LEFT JOIN services_rendered sr on vaia.service_rendered_id = sr.services_rendered_id
                    LEFT JOIN venues ve on vaia.venue_id = ve.venue_id
                    LEFT JOIN field_offices fo on vaia.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE vaia.vpa_association_initiated_activity_id = :id AND vaia.deleted_at IS NULL",
                ['id' => $id]
            )->fetchAssociative();
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(int $id, VpaAssociationInitiatedActivitiesModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (! $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setServiceRenderedId($data->getServicesRenderedId());
        $entity->setVenueDate(
            $this->appDateHelper->convertStringToImmutableDate($data->getVenueDate())
        );
        $entity->setVenueId($data->getVenueId());
        $entity->setVolunteerId($data->getVolunteerId());
        $entity->setRole($data->getRole());
        $entity->setCrdResourcesTapped($data->getCrdResourcesTapped());
        $entity->setCrdAssistanceReceived($data->getCrdAssistanceReceived());
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setQuarterId($data->getQuarterId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }
}
