<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Entity\Sessions;
use App\Model\Sessions as SessionsModel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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
    ){
        parent::__construct($registry, Sessions::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function create(SessionsModel $sessionData): int | null
    {
        $isExist = $this->isSessionExist($sessionData->getQuarterId(), $sessionData->getPhaseId(), $sessionData->getSessionActivityId());

        if ($isExist) {
            return null;
        }

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
     * @throws NonUniqueResultException
     */
    public function isSessionExist(int $quarterId, int $phaseId, int $sessionActivityId): bool
    {
        $session = $this->createQueryBuilder('se')
            ->andWhere('se.quarterId = :quarterId')
            ->andWhere('se.phaseId = :phaseId')
            ->andWhere('se.sessionActivityId = :sessionActivityId')
            ->setParameter('quarterId', $quarterId)
            ->setParameter('phaseId', $phaseId)
            ->setParameter('sessionActivityId', $sessionActivityId)
            ->getQuery()
            ->getOneOrNullResult();

        return $session != null;
    }
}
