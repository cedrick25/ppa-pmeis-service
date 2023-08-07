<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SocialMarketing;
use App\Enum\Response as ResponseEnum;
use App\Model\SocialMarketing as SocialMarketingModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SocialMarketing|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketing|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketing[]    findAll()
 * @method SocialMarketing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "social_marketing";

    public function __construct(
        ManagerRegistry                $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper            $cacheHelper,
        private Helper                 $helper,
        private AppDateHelper          $appDateHelper,
    ) {
        parent::__construct($registry, SocialMarketing::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSocialMarketingKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('sm')
                ->where('sm.deletedAt IS NULL')
                ->orderBy('sm.socialMarketingId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @param string $type
     * @param int $page
     * @param int $pageSize
     * @param int $fieldOfficeId
     * @return array<string, mixed>
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function paginated(string $type, int $page = 1, int $pageSize = 10, int $fieldOfficeId): array
    {
        $params = [
            'cacheKey' => 'social_marketing_' . $type . '_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($type, $pageSize, $page, $fieldOfficeId) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT sm.*, fo.name field_office, r.region_id, r.name region FROM social_marketing as sm
                    LEFT JOIN field_offices fo on sm.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE sm.type = '$type' AND sm.deleted_at IS NULL ";

            if ($fieldOfficeId > 0) {
                $sql .= "AND sm.field_office_id = $fieldOfficeId ";
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
     * @throws \Exception
     */
    public function create(SocialMarketingModel $data): int|null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new SocialMarketing();
        $entity->setSocialMarketingActivityId($data->getSocialMarketingActivityId());
        $entity->setActivityName($data->getActivityName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setType($data->getType());
        $entity->setRemarks($data->getRemarks());
        $entity->setPrimers($data->getPrimers());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getSocialMarketingId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, SocialMarketingModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setSocialMarketingActivityId($data->getSocialMarketingActivityId());
        $entity->setActivityName($data->getActivityName());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setType($data->getType());
        $entity->setRemarks($data->getRemarks());
        $entity->setPrimers($data->getPrimers());
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
        $socialMarketing = $this->isExistingById($id);
        if (! $socialMarketing) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($socialMarketing);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | SocialMarketing
    {
        $socialMarketing = $this->findOneBy([
            'socialMarketingId' => $id,
            'deletedAt' => null
        ]);

        return ($socialMarketing == null) ? false : $socialMarketing;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @param string $type
     * @return array<int|string, array<int, array<string, mixed>>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId, string $type): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sm.*, fo.name as field_office, sma.name as social_marketing_activity FROM social_marketing as sm
                LEFT JOIN field_offices as fo ON sm.field_office_id = fo.field_office_id
                LEFT JOIN social_marketing_activities as sma ON sm.social_marketing_activity_id = sma.id
                WHERE sm.field_office_id = :field_office_id AND sm.type = :type
                  AND sm.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'type' => $type,
                    'field_office_id' => $fieldOfficeId,
                ],
            )->fetchAllAssociative();
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int|string, array<int, array<string, mixed>>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDateRangeWithoutType(array $minMaxDate, int $fieldOfficeId): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sm.*, fo.name as field_office, sma.name as social_marketing_activity FROM social_marketing as sm
                LEFT JOIN field_offices as fo ON sm.field_office_id = fo.field_office_id
                LEFT JOIN social_marketing_activities as sma ON sm.social_marketing_activity_id = sma.id
                WHERE sm.field_office_id = :field_office_id
                  AND sm.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'field_office_id' => $fieldOfficeId,
                ],
            )->fetchAllAssociative();
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @param string $type
     * @return array<int|string, array<int, array<string, mixed>>> | bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId, string $type): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT smpi.person_involved_id FROM social_marketing as sm
                    LEFT JOIN social_marketing_person_involved smpi on sm.social_marketing_id = smpi.social_marketing_id
                    WHERE sm.field_office_id = :field_office_id AND sm.type = :type
                      AND smpi.type = :person_involved_type
                      AND sm.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'type' => $type,
                    'field_office_id' => $fieldOfficeId,
                    'person_involved_type' => 'VPA',
                ]
            )->fetchAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sm.*, fo.name field_office, r.region_id, r.name region
                    FROM social_marketing as sm
                    LEFT JOIN field_offices fo on sm.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE sm.social_marketing_id = :id AND sm.deleted_at IS NULL ",
                ['id' => $id]
            )->fetchAssociative();
    }
}
