<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\RJProcessStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

/**
 * @method RJProcessStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJProcessStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJProcessStatus[]    findAll()
 * @method RJProcessStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJProcessStatusRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_process_status";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, RJProcessStatus::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return RJProcessStatus[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJProcessStatusKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('rps')
                ->where('rps.deletedAt IS NULL')
                ->orderBy('rps.idRJProcessStatus', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | RJProcessStatus
    {
        $RJProcessStatus = $this->findOneBy([
            'idRJProcessStatus' => $id,
            'deletedAt' => null
        ]);

        return ($RJProcessStatus == null) ? false : $RJProcessStatus;
    }
}
