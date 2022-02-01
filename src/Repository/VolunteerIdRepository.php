<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\VolunteerId;
use App\Model\VolunteerId as VolunteerIdModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method VolunteerId|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerId|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerId[]    findAll()
 * @method VolunteerId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerIdRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "volunteer_id";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, VolunteerId::class);
    }

    /**
     * @return VolunteerId[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVolunteerIdsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('vi')
                ->orderBy('vi.idNo', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @param VolunteerIdModel $data
     * @return string|null
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(VolunteerIdModel $data): string | null
    {
        $isExist = $this->isExistingById($data->getIdNo());

        if ($isExist) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newVolunteerId = new VolunteerId();
        $newVolunteerId->setIdNo($data->getIdNo());
        $newVolunteerId->setVolunteerId($data->getVolunteerId());
        $newVolunteerId->setAdminName($data->getAdminName());

        $this->getEntityManager()->persist($newVolunteerId);
        $this->getEntityManager()->flush();

        return $newVolunteerId->getIdNo();
    }

    public function isExistingById(string $id): bool | VolunteerId
    {
        $volunteerId = $this->findOneBy([
            'idNo' => $id
        ]);

        return ($volunteerId == null) ? false : $volunteerId;
    }
}
