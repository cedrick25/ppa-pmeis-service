<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\CapabilityBuilding;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method CapabilityBuilding|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapabilityBuilding|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapabilityBuilding[]    findAll()
 * @method CapabilityBuilding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapabilityBuildingRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "capability_building";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, CapabilityBuilding::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllCapabilityBuildingsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('cb')
                ->where('cb.deletedAt IS NULL')
                ->orderBy('cb.capabilityBuildingId', 'DESC')
                ->getQuery()
                ->getResult();
        });
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
            'cacheKey' => 'capability_building_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT cb.*, fo.name field_office, r.region_id, r.name region
                    FROM capability_building as cb
                    LEFT JOIN field_offices fo on cb.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE cb.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function create(array $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);
        $participantCount = count($data['participants']);

        $entity = new CapabilityBuilding();
        $entity->setType($data['type']);
        $entity->setSubtype($data['subtype']);
        $entity->setTitle($data['title']);
        $entity->setStartDate(
            $this->appDateHelper->convertStringToImmutableDate($data['startDate'])
        );
        $entity->setEndDate($this->appDateHelper->convertStringToImmutableDate($data['endDate']));
        $entity->setNoOfParticipants($participantCount);
        $entity->setIsPwd(false);
        $entity->setIsSeniorCitizen(false);
        $entity->setNotManagerialSupervisory($data['notManagerialSupervisory'] ?? '');
        $entity->setNotTechnical($data['notTechnical'] ?? '');
        $entity->setNotFoundation($data['notFoundation'] ?? '');
        $entity->setNoOfTrainingHours($data['noOfTrainingHours']);
        $entity->setTcInHouse($data['tcInHouse'] ?? '');
        $entity->setTcOutHouse($data['tcOutHouse'] ?? '');
        $entity->setFieldOfficeId($data['fieldOfficeId']);
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getCapabilityBuildingId();
    }

    public function isExistingById(int $id): bool | CapabilityBuilding
    {
        $entity = $this->findOneBy([
            'capabilityBuildingId' => $id,
            'deletedAt' => null
        ]);

        return ($entity == null) ? false : $entity;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findReport(array $minMaxDate, int $fieldOfficeId, string $type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT cb.* FROM capability_building as cb
                WHERE cb.field_office_id = $fieldOfficeId
                  AND cb.type = '$type'
                  AND cb.start_date >= CAST('$min' AS DATE) AND cb.end_date <= CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $entity = $this->isExistingById($id);
        if (! $entity) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, array $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (! $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $participantCount = count($data['participants']);

        $entity->setType($data['type']);
        $entity->setSubtype($data['subtype']);
        $entity->setTitle($data['title']);
        $entity->setStartDate(
            $this->appDateHelper->convertStringToImmutableDate($data['startDate'])
        );
        $entity->setEndDate($this->appDateHelper->convertStringToImmutableDate($data['endDate']));
        $entity->setNoOfParticipants($participantCount);
        $entity->setIsPwd(false);
        $entity->setIsSeniorCitizen(false);
        $entity->setNotManagerialSupervisory($data['notManagerialSupervisory'] ?? '');
        $entity->setNotTechnical($data['notTechnical'] ?? '');
        $entity->setNotFoundation($data['notFoundation'] ?? '');
        $entity->setNoOfTrainingHours($data['noOfTrainingHours']);
        $entity->setTcInHouse($data['tcInHouse'] ?? '');
        $entity->setTcOutHouse($data['tcOutHouse'] ?? '');
        $entity->setFieldOfficeId($data['fieldOfficeId']);
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function getById(int $id): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT cb.*, fo.name field_office, r.region_id, r.name region
                    FROM capability_building as cb
                    LEFT JOIN field_offices fo on cb.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE cb.capability_building_id = :id AND cb.deleted_at IS NULL",
                ['id' => $id],
            )->fetchAssociative();
    }
}
