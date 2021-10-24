<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\ClientTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method ClientTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTypes[]    findAll()
 * @method ClientTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTypesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
    ){
        parent::__construct($registry, ClientTypes::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function create(string $code, string $description): int | null
    {
        $clientType = $this->findOneBy(['code' => $code]);

        if ($clientType != null) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllClientTypesKey());

        $newClientType = new ClientTypes();
        $newClientType->setCode($code);
        $newClientType->setDescription($description);

        $this->getEntityManager()->persist($newClientType);
        $this->getEntityManager()->flush();

        return $newClientType->getClientTypeId();
    }
}
