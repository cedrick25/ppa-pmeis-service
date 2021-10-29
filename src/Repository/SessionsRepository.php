<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Sessions;
use App\Model\Sessions as SessionsModel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Sessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sessions[]    findAll()
 * @method Sessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionsRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
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
        $isExist = $this->isSessionExist(
            $sessionData->getQuarterId(),
            $sessionData->getPhaseId(),
            $sessionData->getSessionActivityId());

        if ($isExist) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllSessionsKey());

        $session = new Sessions();
        $session->setQuarterId($sessionData->getQuarterId());
        $session->setRegionId($sessionData->getRegionId());
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
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllSessionsKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

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
        $session = $this->getById($id);

        if ($session == null) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->delete($this->cacheHelper->getAllSessionsKey());

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
        $session = $this->getById($id);

        if ($session == null) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->delete($this->cacheHelper->getAllSessionsKey());

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
    public function update(int $id, SessionsModel $sessionData): bool
    {
        $session = $this->getById($id);

        if ($session == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllSessionsKey());

        // quarter, phase and session activity cannot be updated to avoid conflict
        // if needed, create new one instead
        $session->setRegionId($sessionData->getRegionId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function getById(int $id): ?Sessions
    {
        return $this->findOneBy([
            'sessionId' => $id,
            'deletedAt' => null
        ]);
    }

    public function isSessionExist(int $quarterId, int $phaseId, int $sessionActivityId): bool
    {
        $session = $this->findOneBy([
            'quarterId' => $quarterId,
            'phaseId' => $phaseId,
            'sessionActivityId' => $sessionActivityId,
            'deletedAt' => null
        ]);

        return $session != null;
    }
}
