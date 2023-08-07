<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SpecialAssignment;
use App\Enum\Response as ResponseEnum;
use App\Model\SpecialAssignment as SpecialAssignmentModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SpecialAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialAssignment[]    findAll()
 * @method SpecialAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialAssignmentRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "special_assignments";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, SpecialAssignment::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(SpecialAssignmentModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new SpecialAssignment();
        $entity->setStartDate($this->appDateHelper->convertStringToImmutableDate($data->getStartDate()));
        $entity->setEndDate($this->appDateHelper->convertStringToImmutableDate($data->getEndDate()));
        $entity->setCategoryType($data->getCategoryType());
        $entity->setSubType($data->getSubType());
        $entity->setDecsription($data->getDecsription());
        $entity->setActivity($data->getActivity());
        $entity->setVenue($data->getVenue());
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getSpecialAssignmentId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, SpecialAssignmentModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setStartDate($this->appDateHelper->convertStringToImmutableDate($data->getStartDate()));
        $entity->setEndDate($this->appDateHelper->convertStringToImmutableDate($data->getEndDate()));
        $entity->setCategoryType($data->getCategoryType());
        $entity->setSubType($data->getSubType());
        $entity->setDecsription($data->getDecsription());
        $entity->setActivity($data->getActivity());
        $entity->setVenue($data->getVenue());
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
            'cacheKey' => $this->cacheHelper->getAllSpecialAssignmentsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('sa')
                ->where('sa.deletedAt IS NULL')
                ->orderBy('sa.specialAssignmentId', 'DESC')
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
            'cacheKey' => 'special_assignment_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page, $fieldOfficeId) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT sa.*, fo.name field_office, r.region_id, r.name region FROM special_assignment sa
                    LEFT JOIN field_offices fo on sa.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE sa.deleted_at IS NULL ";

            if ($fieldOfficeId > 0) {
                $sql .= "AND sa.field_office_id = $fieldOfficeId ";
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
        $specialAssignment= $this->isExistingById($id);

        if (! $specialAssignment) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($specialAssignment);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | SpecialAssignment
    {
        $specialAssignment = $this->findOneBy([
            'specialAssignmentId' => $id,
            'deletedAt' => null
        ]);

        return ($specialAssignment == null) ? false : $specialAssignment;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sa.* FROM special_assignment as sa WHERE sa.field_office_id = :field_office_id
                AND sa.end_date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE) ORDER BY sa.end_date DESC",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'field_office_id' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sa.*, fo.name field_office, r.region_id, r.name region FROM special_assignment sa
                    LEFT JOIN field_offices fo on sa.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE sa.special_assignment_id = :id AND sa.deleted_at IS NULL",
                ['id' => $id],
            )->fetchAssociative();
    }
}
