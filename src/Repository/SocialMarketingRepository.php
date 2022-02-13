<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SocialMarketing;
use App\Model\SocialMarketing as SocialMarketingModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SocialMarketing|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketing|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketing[]    findAll()
 * @method SocialMarketing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "social_marketing";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, SocialMarketing::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSocialMarketingKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('sm')
                ->where('sm.deletedAt IS NULL')
                ->orderBy('sm.socialMarketingId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(SocialMarketingModel $data): int|null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newSocialMarketing = new SocialMarketing();
        $newSocialMarketing->setSocialMarketingActivityId($data->getSocialMarketingActivityId());
        $newSocialMarketing->setActivityName($data->getActivityName());
        $newSocialMarketing->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newSocialMarketing->setVenue($data->getVenue());
        $newSocialMarketing->setParticipants($data->getParticipants());
        $newSocialMarketing->setType($data->getType());
        $newSocialMarketing->setPersonnelId($data->getPersonnelId());
        $newSocialMarketing->setPersonnelRole($data->getPersonnelRole());
        $newSocialMarketing->setVpaId($data->getVpaId());
        $newSocialMarketing->setVpaRole($data->getVpaRole());
        $newSocialMarketing->setRemarks($data->getRemarks());
        $newSocialMarketing->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newSocialMarketing);
        $this->getEntityManager()->flush();

        return $newSocialMarketing->getSocialMarketingId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $socialMarketing = $this->isExistingById($id);
        if (! $socialMarketing) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($socialMarketing);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | SocialMarketing
    {
        $socialMarketing = $this->findOneBy([
            'socialMarketingId' => $id,
            'deletedAt' => null
        ]);

        return ($socialMarketing == null) ? false : $socialMarketing;
    }
}
