<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\AuditTrail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method AuditTrail|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTrail|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTrail[]    findAll()
 * @method AuditTrail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTrailRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "audit_trail";

    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private TagAwareCacheInterface $cache,
    ) {
        parent::__construct($registry, AuditTrail::class);
    }

    /**
     * @param array<string, mixed> $userDetails
     * @param array<string, mixed> $actionDetails
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(
        string $action,
        array $userDetails,
        array $actionDetails,
    ): void {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $auditTrail = new AuditTrail();
        $auditTrail->setAction($action);
        $auditTrail->setUserId($userDetails['userId']);
        $auditTrail->setEmail($userDetails['email']);
        $auditTrail->setFirstName($userDetails['firstName']);
        $auditTrail->setMiddleName($userDetails['middleName']);
        $auditTrail->setLastName($userDetails['lastName']);
        $auditTrail->setActionDetails($actionDetails);
        $auditTrail->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($auditTrail);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int[] $ids
     * @throws \Doctrine\DBAL\Exception
     */
    public function batchDelete(array $ids): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->_em->getConnection()->executeQuery(
            "DELETE FROM audit_trail WHERE audit_trail_id IN (:ids)",
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );
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
            'cacheKey' => $this->cacheHelper->getPositionsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('at');
        });
    }
}
