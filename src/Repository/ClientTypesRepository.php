<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\ClientTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method ClientTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTypes[]    findAll()
 * @method ClientTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTypesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "client_types";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
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

        $this->cache->invalidateTags([self::CACHE_TAG]);

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
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllClientTypesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->findAll();
        });
    }

    /**
     * @param int $id
     * @return bool
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id): bool
    {
        $clientType = $this->find($id);

        if ($clientType == null) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($clientType);
        $this->getEntityManager()->flush();

        return true;
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
            'cacheKey' => $this->cacheHelper->getClientTypesPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('ct')->orderBy('ct.clientTypeId');
        });
    }

    public function isExistingById(int $id): bool | ClientTypes
    {
        $clientType = $this->find($id);

        return ($clientType == null) ? false : $clientType;
    }
}
