<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SpecialAssignment;
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

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('sa')
                ->where('sa.deletedAt IS NULL')
                ->orderBy('sa.specialAssignmentId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Exception
     */
    public function create(SpecialAssignmentModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newSpecialAssignment = new SpecialAssignment();
        $newSpecialAssignment->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newSpecialAssignment->setCategoryType($data->getCategoryType());
        $newSpecialAssignment->setSubType($data->getSubType());
        $newSpecialAssignment->setDecsription($data->getDecsription());
        $newSpecialAssignment->setActivity($data->getActivity());
        $newSpecialAssignment->setVenue($data->getVenue());
        $newSpecialAssignment->setPersonInvolved($data->getPersonInvolved());
        $newSpecialAssignment->setRemarks($data->getRemarks());
        $newSpecialAssignment->setFieldOfficeId($data->getFieldOfficeId());
        $newSpecialAssignment->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newSpecialAssignment);
        $this->getEntityManager()->flush();

        return $newSpecialAssignment->getSpecialAssignmentId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
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
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT sa.* FROM special_assignment as sa
                WHERE sa.field_office_id = $fieldOfficeId AND sa.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)
                ORDER BY sa.date DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
