<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\RJProcesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method RJProcesses|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJProcesses|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJProcesses[]    findAll()
 * @method RJProcesses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJProcessesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_process";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, RJProcesses::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return RJProcesses[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJProcessKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('rp')
                ->where('rp.deletedAt IS NULL')
                ->orderBy('rp.idRJProcesses', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | RJProcesses
    {
        $RJProcess = $this->findOneBy([
            'idRJProcesses' => $id,
            'deletedAt' => null
        ]);

        return ($RJProcess == null) ? false : $RJProcess;
    }
}
