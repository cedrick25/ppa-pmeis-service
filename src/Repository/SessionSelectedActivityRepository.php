<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SessionSelectedActivities;
use App\Enum\Response as ResponseEnum;
use App\Model\SessionSelectedActivity as SessionSelectedActivityModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SessionSelectedActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionSelectedActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionSelectedActivities[]    findAll()
 * @method SessionSelectedActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionSelectedActivityRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "session_selected_activities";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
        private ClientsRepository $clientsRepository,
    ) {
        parent::__construct($registry, SessionSelectedActivities::class);
    }



    /**
     * @param int $sessionId
     * @param array<string, mixed> $attendees
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function batchCreate(int $sessionId, array $activities): void
    {
        $sessionActivityId = [];
        foreach ($activities as $activity) {
            $sessionActivity = new SessionSelectedActivities();
            $sessionActivity->setSessionId($sessionId);
            $sessionActivity->setSessionActivityId($activity['sessionActivityId']);
            $sessionActivity->setTreatmentCategoryId($activity['treatmentCategoryId']);
            $sessionActivity->setActivityDetail($activity['activityDetail'] ?? '');
            $sessionActivity->setIsCommunityService($activity['isCommunityService'] ?? false);
            $sessionActivity->setIsTreePlanting($activity['isTreePlanting'] ?? false);
            $sessionActivity->setIsCooperativeSelfHelp($activity['isCooperativeSelfHelp'] ?? false);
            $sessionActivity->setIsCooperativeSelfHelpActivities($activity['isCooperativeSelfHelpActivities'] ?? false);
            $this->getEntityManager()->persist($sessionActivity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(SessionSelectedActivities::class);

        $this->cache->invalidateTags([self::CACHE_TAG]);
    }



    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteBySessionId(int $sessionId): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "DELETE FROM session_selected_activities WHERE session_id = $sessionId";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }


    /**
     * @return SessionSelectedActivity[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function listBySessionId(int $id): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllActivitySessionsBySessionIdKey($id),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () use ($id) {
            return $this->createQueryBuilder('ssa')
                ->where('ssa.sessionId = :id')
                ->setParameter('id', $id)
                ->orderBy('ssa.sessionSelectedActivityId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $sessionActivity = $this->isExistingById($id);
        if (! $sessionActivity) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($sessionActivity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ClientSessionModel $clientSessionData): string
    {
        $clientSession = $this->isExistingById($id);

        if (! $clientSession) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($clientSession, $clientSessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $clientSession->setClientId($clientSessionData->getClientId());
        $clientSession->setSessionId($clientSessionData->getSessionId());
        $clientSession->setRole($clientSessionData->getRole());

        $this->getEntityManager()->persist($clientSession);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | SessionSelectedActivity
    {
        $sessionActivity = $this->findOneBy([
            'sessionSelectedActivityId' => $id
        ]);

        return ($sessionActivity == null) ? false : $sessionActivity;
    }




}
