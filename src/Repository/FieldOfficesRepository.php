<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\FieldOffices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method FieldOffices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldOffices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldOffices[]    findAll()
 * @method FieldOffices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldOfficesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
    ){
        parent::__construct($registry, FieldOffices::class);
    }

    /**
     * @return FieldOffices[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllFieldOfficesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('fo')
                ->orderBy('fo.fieldOfficeId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }
}
