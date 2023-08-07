<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ResourceMobilization;
use App\Enum\Response as ResponseEnum;
use App\Model\ResourceMobilization as ResourceMobilizationModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method ResourceMobilization|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceMobilization|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceMobilization[]    findAll()
 * @method ResourceMobilization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceMobilizationRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "resource_mobilizations";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, ResourceMobilization::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllResourceMobilizationKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('rm')
                ->where('rm.deletedAt IS NULL')
                ->orderBy('rm.resourceMobilizationId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param int $fieldOfficeId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10, int $fieldOfficeId): array
    {
        $params = [
            'cacheKey' => 'res_mob_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page, $fieldOfficeId) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rm.*, fo.name field_office, r.region_id, r.name region
                    FROM resource_mobilization as rm
                    LEFT JOIN field_offices fo on rm.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE rm.deleted_at IS NULL ";

                if ($fieldOfficeId > 0) {
                    $sql .= "AND rm.field_office_id = $fieldOfficeId ";
                }

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
     * @throws Exception
     * @throws \Exception
     */
    public function create(ResourceMobilizationModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new ResourceMobilization();
        $entity->setCategory($data->getCategory());
        $entity->setActivityName($data->getActivityName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getResourceMobilizationId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Exception
     */
    public function update(int $id, ResourceMobilizationModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (! $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setCategory($data->getCategory());
        $entity->setActivityName($data->getActivityName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $resourceMobilization = $this->isExistingById($id);
        if (! $resourceMobilization) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($resourceMobilization);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | ResourceMobilization
    {
        $resourceMobilization = $this->findOneBy([
            'resourceMobilizationId' => $id,
            'deletedAt' => null
        ]);

        return ($resourceMobilization == null) ? false : $resourceMobilization;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT rm.* FROM resource_mobilization as rm WHERE rm.field_office_id = $fieldOfficeId
                AND rm.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)
                ORDER BY rm.category DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rm.*, fo.name field_office, r.region_id, r.name region FROM resource_mobilization as rm
                    LEFT JOIN field_offices fo on rm.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE rm.resource_mobilization_id = :id AND rm.deleted_at IS NULL",
                ['id' => $id],
            )->fetchAssociative();
    }

    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId): ?array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT rmsb.secured_by_id FROM resource_mobilization rm
                        LEFT JOIN res_mob_secured_by rmsb ON rm.resource_mobilization_id = rmsb.res_mob_id
                        WHERE rm.field_office_id = :fieldOfficeId
                        AND rmsb.type = :type
                        AND rm.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)
                        AND rm.deleted_at IS NULL",
                [
                    'min' => (string) $minMaxDate['min'],
                    'max' => (string) $minMaxDate['max'],
                    'type' => 'VPA',
                    'fieldOfficeId' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }
}
