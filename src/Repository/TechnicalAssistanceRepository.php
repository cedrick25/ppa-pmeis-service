<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\TechnicalAssistance;
use App\Enum\Response as ResponseEnum;
use App\Model\TechnicalAssistance as TechnicalAssistanceModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method TechnicalAssistance|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalAssistance|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalAssistance[]    findAll()
 * @method TechnicalAssistance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalAssistanceRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "technical_assistance";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, TechnicalAssistance::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllTechnicalAssistanceKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('ta')
                ->where('ta.deletedAt IS NULL')
                ->orderBy('ta.id', 'DESC')
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
            'cacheKey' => 'technical_assistance_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT ta.*, fo.name field_office, r.region_id, r.name region
                    FROM technical_assistance as ta
                    LEFT JOIN field_offices fo on ta.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE ta.deleted_at IS NULL ";

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
     * @throws \Exception
     */
    public function create(TechnicalAssistanceModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new TechnicalAssistance();
        $entity->setActivityName($data->getActivityName());
        $entity->setAgencyName($data->getAgencyName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setAssistanceType($data->getAssistanceType()['value']);
        $entity->setRemarks($data->getRemarks());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getId();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $technicalAssistance = $this->isExistingById($id);
        if (! $technicalAssistance) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($technicalAssistance);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | TechnicalAssistance
    {
        $technicalAssistance = $this->findOneBy([
            'id' => $id,
            'deletedAt' => null
        ]);

        return ($technicalAssistance == null) ? false : $technicalAssistance;
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

        $sql = "SELECT ta.*, fo.name as field_office FROM technical_assistance as ta
                LEFT JOIN field_offices as fo ON ta.field_office_id = fo.field_office_id
                WHERE ta.field_office_id = $fieldOfficeId
                AND ta.date BETWEEN CAST('$min' AS DATE)
                AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT ta.*, fo.name field_office, r.region_id, r.name region
                    FROM technical_assistance as ta
                    LEFT JOIN field_offices fo on ta.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE ta.id = :id AND ta.deleted_at IS NULL ",
                ['id' => $id]
            )->fetchAssociative();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, TechnicalAssistanceModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setActivityName($data->getActivityName());
        $entity->setAgencyName($data->getAgencyName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setAssistanceType($data->getAssistanceType()['value']);
        $entity->setRemarks($data->getRemarks());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId): ?array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT tapi.persons_involved_id FROM technical_assistance ta
                        LEFT JOIN technical_assistance_persons_involved tapi ON ta.id = tapi.technical_assistance_id
                        WHERE ta.field_office_id = :fieldOfficeId
                        AND tapi.type = :type
                        AND ta.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)
                        AND ta.deleted_at IS NULL",
                [
                    'min' => (string) $minMaxDate['min'],
                    'max' => (string) $minMaxDate['max'],
                    'type' => 'VPA',
                    'fieldOfficeId' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }
}
