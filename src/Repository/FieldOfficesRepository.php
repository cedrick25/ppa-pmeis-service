<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\FieldOffices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

/**
 * @method FieldOffices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldOffices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldOffices[]    findAll()
 * @method FieldOffices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldOfficesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "field_offices";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, FieldOffices::class);
    }

    /**
     * @return FieldOffices[]
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllFieldOfficesKey(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT fo.*, rg.name as region_name FROM field_offices as fo " .
                "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
                "WHERE fo.deleted_at IS NULL ORDER BY fo.field_office_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
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
            'cacheKey' => $this->cacheHelper->getFieldOfficePaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT fo.*, rg.name as region_name FROM field_offices as fo
                    LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                    WHERE fo.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @return array<string, mixed> | null
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getByRegion(int $regionId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getFieldOfficeByRegionKey($regionId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($regionId) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT fo.*, rg.name as region_name FROM field_offices as fo " .
                "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
                "WHERE fo.region_id = $regionId AND fo.deleted_at IS NULL ORDER BY fo.field_office_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @param int $id
     * @return array<string, mixed>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getRegionByFieldOfficeId(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rg.name as region_name, rg.region_id FROM field_offices as fo
                LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                WHERE fo.field_office_id = $id AND fo.deleted_at IS NULL ORDER BY fo.field_office_id DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAssociative();
    }

    public function findNamesByFieldOfficeIds(array $fieldOfficesId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->executeQuery(
            "SELECT fo.name, fo.field_office_id FROM field_offices as fo WHERE fo.field_office_id IN (:field_offices_id)",
            ['field_offices_id' => $fieldOfficesId],
            ['field_offices_id' => Connection::PARAM_INT_ARRAY]
        );
        $results = $query->fetchAllAssociative();
        $response = [];

        foreach ($results as $result) {
            $response[$result['field_office_id']] = $result['name'];
        }

        return $response;
    }

    public function isExistingById(int $id): bool | FieldOffices
    {
        $fieldOffice = $this->find($id);

        return ($fieldOffice == null) ? false : $fieldOffice;
    }

    /**
     * @param int[] $ids
     * @return int[][]
     * @throws Exception
     */
    public function getRegionIdsByFieldOfficeIds(array $ids): array
    {
        $return = [];
        $query = $this->_em->getConnection()->executeQuery(
            "SELECT region_id, field_office_id FROM field_offices WHERE field_office_id IN (:ids)",
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );
        $results = $query->fetchAllAssociative();

        foreach ($results as $result) {
            $return[$result['region_id']][] = (int) $result['field_office_id'];
        }

        return $return;
    }

    /**
     * @param int $regionId
     * @return int[]
     * @throws Exception
     */
    public function getFieldOfficeIdsByRegionId(int $regionId): array
    {
        $return = [];
        $query = $this->_em->getConnection()->executeQuery(
            "SELECT field_office_id FROM field_offices WHERE region_id = :regionId",
            ['regionId' => $regionId],
        );
        $results = $query->fetchAllAssociative();

        foreach ($results as $result) {
            $return[] = (int) $result['field_office_id'];
        }

        return $return;
    }
}
