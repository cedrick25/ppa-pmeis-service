<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\RJOutcomes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

/**
 * @method RJOutcomes|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJOutcomes|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJOutcomes[]    findAll()
 * @method RJOutcomes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJOutcomesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_outcomes";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, RJOutcomes::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return RJOutcomes[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJOutcomesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('ro')
                ->where('ro.deletedAt IS NULL')
                ->orderBy('ro.rjOutcomeId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | RJOutcomes
    {
        $RJOutcome = $this->findOneBy([
            'rjOutcomeId' => $id,
            'deletedAt' => null
        ]);

        return ($RJOutcome == null) ? false : $RJOutcome;
    }
}
