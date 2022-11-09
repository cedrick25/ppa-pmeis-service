<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SupportOfRegionToFieldOffice;
use App\Model\SupportOfRegionToFieldOffice as SupportOfRegionToFieldOfficeModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SupportOfRegionToFieldOffice|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportOfRegionToFieldOffice|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportOfRegionToFieldOffice[]    findAll()
 * @method SupportOfRegionToFieldOffice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportOfRegionToFieldOfficeRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "support_of_region_to_field_offices";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, SupportOfRegionToFieldOffice::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSupportOfRegionToFieldOfficesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('sortfo')
                ->where('sortfo.deletedAt IS NULL')
                ->orderBy('sortfo.id', 'DESC')
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
            'cacheKey' => 'sortfo_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT sortfo.*, fo.name as field_office, r.name region
                    FROM support_of_region_to_field_office as sortfo
                    LEFT JOIN field_offices as fo ON sortfo.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE sortfo.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @param SupportOfRegionToFieldOfficeModel $data
     * @return int|null
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(SupportOfRegionToFieldOfficeModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newSupportOfRegionToFieldOffice = new SupportOfRegionToFieldOffice();
        $newSupportOfRegionToFieldOffice->setCategory($data->getCategory());
        $newSupportOfRegionToFieldOffice->setSubCategory($data->getSubCategory());
        $newSupportOfRegionToFieldOffice->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newSupportOfRegionToFieldOffice->setFieldOfficeId($data->getFieldOfficeId());
        $newSupportOfRegionToFieldOffice->setParticulars($data->getParticulars());
        $newSupportOfRegionToFieldOffice->setAmount($data->getAmount());
        $newSupportOfRegionToFieldOffice->setAttributableCost($data->getAttributableCost());
        $newSupportOfRegionToFieldOffice->setTotalAmount($data->getTotalAmount());
        $newSupportOfRegionToFieldOffice->setRemarks($data->getRemarks());
        $newSupportOfRegionToFieldOffice->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newSupportOfRegionToFieldOffice);
        $this->getEntityManager()->flush();

        return $newSupportOfRegionToFieldOffice->getId();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $supportOfRegionToFieldOffice = $this->isExistingById($id);
        if (! $supportOfRegionToFieldOffice) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($supportOfRegionToFieldOffice);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | SupportOfRegionToFieldOffice
    {
        $supportOfRegionToFieldOffice = $this->findOneBy([
            'id' => $id,
            'deletedAt' => null
        ]);

        return ($supportOfRegionToFieldOffice == null) ? false : $supportOfRegionToFieldOffice;
    }

    /**
     * @param string[] $minMaxDate
     * @param int[] $fieldOfficesId
     * @param string $category
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function findByDateRange(array $minMaxDate, array $fieldOfficesId, string $category): array
    {
        $conn = $this->getEntityManager()->getConnection();

        return $conn->executeQuery(
            "SELECT sortfo.*, fo.name as field_office, r.name region FROM support_of_region_to_field_office as sortfo
                LEFT JOIN field_offices as fo ON sortfo.field_office_id = fo.field_office_id
                LEFT JOIN regions r on fo.region_id = r.region_id
                WHERE sortfo.field_office_id IN (:fieldOfficesId)
                  AND sortfo.category = :category
                  AND sortfo.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)",
            [
                'fieldOfficesId' => $fieldOfficesId,
                'category' => $category,
                'min' => $minMaxDate['min'],
                'max' => $minMaxDate['max'],
            ],
            ['fieldOfficesId' => Connection::PARAM_INT_ARRAY]
        )->fetchAllAssociative();
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDateRangeAndAllCategory(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT sortfo.*, fo.name as field_office FROM support_of_region_to_field_office as sortfo
                LEFT JOIN field_offices as fo ON sortfo.field_office_id = fo.field_office_id
                WHERE sortfo.field_office_id = $fieldOfficeId
                  AND sortfo.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
