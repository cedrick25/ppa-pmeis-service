<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\Phases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Phases|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phases|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phases[]    findAll()
 * @method Phases[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhasesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
    ){
        parent::__construct($registry, Phases::class);
    }

    /**
     * @return Phases[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllPhasesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('ph')
                ->orderBy('ph.phaseId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }
}
