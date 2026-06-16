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
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(
        int $page = 1,
        int $pageSize = 10,
        ?string $searchColumn = '',
        ?string $searchValue = '',
        ?string $jsonColumn = ''): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAuditTrailPaginatedKey($page, $pageSize, $searchColumn, $searchValue, $jsonColumn),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use($pageSize, $page, $searchColumn, $searchValue, $jsonColumn) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT BIN_TO_UUID(audit_trail_id, true) as audit_trail_id, `action`, user_id, action_details, created_at,
                    email, first_name, middle_name, last_name FROM audit_trail ";

            if (! empty($searchColumn) && ! empty($searchValue)) {
                if ('action_details' === $searchColumn && ! empty($jsonColumn)) {
                    $sql .= "WHERE JSON_EXTRACT($searchColumn, '$.$jsonColumn') LIKE :searchValue ";
                } else {
                    $sql .= "WHERE $searchColumn LIKE :searchValue ";
                }
            }

            $bindValue = [];

            if (! empty($searchColumn) && ! empty($searchValue)) {
                $bindValue = [
                    'searchValue' => [
                        '%' . $searchValue . '%',
                        \PDO::PARAM_STR
                    ]
                ];
            }

            // Get the total here before appending the limit
            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql, $bindValue);

            $sql .=  "ORDER BY created_at DESC LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);

            foreach ($bindValue as $key=>$value) {
                $stmt->bindValue($key,  $value[0], $value[1]);
            }

            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }
}
