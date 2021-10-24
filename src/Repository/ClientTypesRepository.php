<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\ClientTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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

    /**
     * @return ClientTypes[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllClientTypesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->findAll();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $clientType = $this->find($id);

        if ($clientType == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllClientTypesKey());

        $this->getEntityManager()->remove($clientType);
        $this->getEntityManager()->flush();

        return true;
    }
}
