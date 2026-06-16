<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\Phases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Phases|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phases|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phases[]    findAll()
 * @method Phases[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhasesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "phases";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Phases::class);
    }

    /**
     * @return Phases[]
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllPhasesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('ph')
                ->orderBy('ph.phaseId', 'DESC')
                ->getQuery()
                ->getResult();
        });
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
            'cacheKey' => $this->cacheHelper->getPhasesPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('ph')->orderBy('ph.phaseId');
        });
    }

    public function isExistingById(int $id): bool | Phases
    {
        $phase = $this->find($id);

        return ($phase == null) ? false : $phase;
    }
}

