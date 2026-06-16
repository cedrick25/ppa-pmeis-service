<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\JailDecongestion;
use App\Entity\ResourceMobilization;
use App\Enum\Response as ResponseEnum;
use App\Model\JailDecongestion as JailDecongestionModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method JailDecongestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method JailDecongestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method JailDecongestion[]    findAll()
 * @method JailDecongestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JailDecongestionRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "jail_decongestions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, JailDecongestion::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(JailDecongestionModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new JailDecongestion();
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setNameAddress($data->getNameAddress());
        $entity->setJailVenue($data->isJailVenue());
        $entity->setJailOffice($data->isJailOffice());
        $entity->setProbation($data->getProbation());
        $entity->setClemency($data->getClemency());
        $entity->setReferralPao($data->getReferralPao());
        $entity->setReferralProsecution($data->getReferralProsecution());
        $entity->setReferralOthers($data->getReferralOthers());
        $entity->setGcta($data->getGcta());
        $entity->setRecognizance($data->getRecognizance());
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getJailDecongestionId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, JailDecongestionModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setNameAddress($data->getNameAddress());
        $entity->setJailVenue($data->isJailVenue());
        $entity->setJailOffice($data->isJailOffice());
        $entity->setProbation($data->getProbation());
        $entity->setClemency($data->getClemency());
        $entity->setReferralPao($data->getReferralPao());
        $entity->setReferralProsecution($data->getReferralProsecution());
        $entity->setReferralOthers($data->getReferralOthers());
        $entity->setGcta($data->getGcta());
        $entity->setRecognizance($data->getRecognizance());
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllJailDecongestionKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('jd')
                ->where('jd.deletedAt IS NULL')
                ->orderBy('jd.jailDecongestionId', 'DESC')
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
            'cacheKey' => 'jail_decongestion_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page, $fieldOfficeId) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT jd.*, fo.name field_office, r.region_id, r.name region
                    FROM jail_decongestion as jd
                    LEFT JOIN field_offices fo on jd.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE jd.deleted_at IS NULL ";
            
            if ($fieldOfficeId > 0) {
                $sql .= "AND jd.field_office_id = $fieldOfficeId ";
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

    public function isExistingById(int $id): bool | JailDecongestion
    {
        $jailDecongestion = $this->findOneBy([
            'jailDecongestionId' => $id,
            'deletedAt' => null
        ]);

        return ($jailDecongestion == null) ? false : $jailDecongestion;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>> | bool
     * @throws Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT jd.* FROM jail_decongestion as jd WHERE jd.field_office_id = :field_office_id
                 AND jd.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE) ORDER BY jd.date DESC",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'field_office_id' => $fieldOfficeId,
                ],
            )->fetchAllAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT jd.*, fo.name field_office, r.region_id, r.name region
                    FROM jail_decongestion jd
                    LEFT JOIN field_offices fo on jd.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE jd.jail_decongestion_id = :id AND jd.deleted_at IS NULL",
                ['id' => $id],
            )->fetchAssociative();
    }

    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId): ?array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT jdpr.person_responsible_id FROM jail_decongestion jd
                        LEFT JOIN jail_decongestion_person_responsible jdpr
                            ON jd.jail_decongestion_id = jdpr.jail_decongestion_id
                        WHERE jd.field_office_id = :fieldOfficeId
                        AND jdpr.type = :type
                        AND jd.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)
                        AND jd.deleted_at IS NULL",
                [
                    'min' => (string) $minMaxDate['min'],
                    'max' => (string) $minMaxDate['max'],
                    'type' => 'VPA',
                    'fieldOfficeId' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }
}
