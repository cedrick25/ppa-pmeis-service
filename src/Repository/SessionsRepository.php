<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Sessions;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Sessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sessions[]    findAll()
 * @method Sessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "sessions";

    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Sessions::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function create(SessionsModel $sessionData): int | null
    {
        $isExist = $this->isExisting($sessionData);

        if ($isExist) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session = new Sessions();
        $session->setQuarterId($sessionData->getQuarterId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();

        return $session->getSessionId();
    }

    /**
     * @return Sessions[]
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSessionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('se')
                ->andWhere('se.deletedAt IS NULL')
                ->orderBy('se.sessionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws OptimisticLockException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ORMException
     * @throws NonUniqueResultException
     */
    public function delete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($session);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(int $id, SessionsModel $sessionData): string
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($session, $sessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setQuarterId($sessionData->getQuarterId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Sessions
    {
        $session = $this->findOneBy([
            'sessionId' => $id,
            'deletedAt' => null
        ]);

        return ($session == null) ? false : $session;
    }

    public function isExisting(SessionsModel $sessionData): bool
    {
        $session = $this->findOneBy([
            'quarterId' => $sessionData->getQuarterId(),
            'phaseId' => $sessionData->getPhaseId(),
            'sessionActivityId' => $sessionData->getSessionActivityId(),
            'deletedAt' => null
        ]);

        return $session != null;
    }

    private function isConflicted(Sessions $fetchedSession, SessionsModel $sessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedSession->getQuarterId() === $sessionData->getQuarterId() &&
            $fetchedSession->getPhaseId() === $sessionData->getPhaseId() &&
            $fetchedSession->getSessionActivityId() === $sessionData->getSessionActivityId()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($sessionData)) {
            return true;
        }

        return false;
    }
}
