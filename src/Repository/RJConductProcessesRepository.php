<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Enum\Response as ResponseEnum;
use App\Model\RJConductProcesses as RJConductProcessesModel;
use App\Entity\RJConductProcesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method RJConductProcesses|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJConductProcesses|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJConductProcesses[]    findAll()
 * @method RJConductProcesses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJConductProcessesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_conduct_processes";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, RJConductProcesses::class);
    }

    /**
     * @return RJConductProcesses[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJConductProcessesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('p')
                ->where('p.deletedAt IS NULL')
                ->orderBy('p.rjConductProcessId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws Exception
     */
    public function create(RJConductProcessesModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newRJConductProcesses = new RJConductProcesses();

        $newRJConductProcesses->setClientId($data->getClientId());
        $newRJConductProcesses->setQuarterId($data->getQuarterId());
        $newRJConductProcesses->setFieldOfficeId($data->getFieldOfficeId());
        $newRJConductProcesses->setOffenseId($data->getOffenseId());
        $newRJConductProcesses->setPeVenueId($data->getPeVenueId());
        $newRJConductProcesses->setPeDate($this->appDateHelper->convertStringToImmutableDate($data->getPeDate()));
        $newRJConductProcesses->setPeActivity($data->getPeActivity());
        $newRJConductProcesses->setRjpDate($this->appDateHelper->convertStringToImmutableDate($data->getRjpDate()));
        $newRJConductProcesses->setRjpId($data->getRjpId());
        $newRJConductProcesses->setRjpVenueId($data->getRjpVenueId());
        $newRJConductProcesses->setRjpsId($data->getRjpsId());
        $newRJConductProcesses->setRjoId($data->getRjoId());
        $newRJConductProcesses->setRjGroup($data->getRjGroup());
        $newRJConductProcesses->setPlannerId($data->getPlannerId());
        $newRJConductProcesses->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newRJConductProcesses);
        $this->getEntityManager()->flush();

        return $newRJConductProcesses->getRJConductProcessId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $RJConductProcesses = $this->isExistingById($id);
        if (! $RJConductProcesses) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($RJConductProcesses);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $RJConductProcesses =$this->isExistingById($id);

        if ($RJConductProcesses == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $RJConductProcesses->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | RJConductProcesses
    {
        $RJConductProcesses = $this->findOneBy([
            'rjConductProcessId' => $id,
            'deletedAt' => null
        ]);

        return ($RJConductProcesses == null) ? false : $RJConductProcesses;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getRJIB1Data(int $quarterId, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getRJIB1Key($quarterId, $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use($quarterId, $fieldOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rjcp.*, c.first_name, c.middle_name, c.last_name, c.gender, c.is_pwd, c.is_senior_citizen,
                    o.name as offense, rjcp.pe_date, (SELECT name FROM venues WHERE venues.venue_id = rjcp.pe_venue_id) as pe_venue,
                    rjcp.pe_activity, rjcp.pe_date, (SELECT name FROM venues WHERE venues.venue_id = rjcp.rjp_venue_id) as rjp_venue,
                    rjp.name as rjp_type, ud.first_name as planner_fn, ud.middle_name as planner_mn, ud.last_name as planner_ln,
                    rjps.name as rjp_status, ro.name as rj_outcome_name, ro.code as rj_outcome_code
                    FROM rjconduct_processes as rjcp 
                LEFT JOIN clients as c ON rjcp.client_id = c.client_id
                LEFT JOIN offenses as o ON rjcp.offense_id = o.offenses_id
                LEFT JOIN rjprocesses as rjp ON rjcp.rjp_id = rjp.id_rjprocesses
                LEFT JOIN user_details as ud ON rjcp.planner_id = ud.user_account_id
                LEFT JOIN rjprocess_status as rjps ON rjcp.rjps_id = rjps.id_rjprocess_status
                LEFT JOIN rjoutcomes as ro ON rjcp.rjo_id = ro.rj_outcome_id
                WHERE rjcp.quarter_id = $quarterId AND rjcp.field_office_id = $fieldOfficeId
                AND rjcp.deleted_at IS NULL ORDER BY rjcp.rj_group";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    public function update(int $id, RJConductProcessesModel $data): string
    {
        $entity = $this->find($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity->setClientId($data->getClientId());
        $entity->setQuarterId($data->getQuarterId());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setOffenseId($data->getOffenseId());
        $entity->setPeVenueId($data->getPeVenueId());
        $entity->setPeDate($this->appDateHelper->convertStringToImmutableDate($data->getPeDate()));
        $entity->setPeActivity($data->getPeActivity());
        $entity->setRjpDate($this->appDateHelper->convertStringToImmutableDate($data->getRjpDate()));
        $entity->setRjpId($data->getRjpId());
        $entity->setRjpVenueId($data->getRjpVenueId());
        $entity->setRjpsId($data->getRjpsId());
        $entity->setRjoId($data->getRjoId());
        $entity->setRjGroup($data->getRjGroup());
        $entity->setPlannerId($data->getPlannerId());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function getVolunteerIdsByQuarterAndFieldOffice(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rcppi.persons_involved_id FROM rjconduct_processes as rp
                LEFT JOIN rj_conducted_process_persons_involved as rcppi
                    ON rp.rj_conduct_process_id = rcppi.rj_conducted_process_id
                WHERE rp.quarter_id = :quarter_id AND rp.field_office_id = :field_office_id
                  AND rcppi.type = 'VPA'
                  AND rp.deleted_at IS NULL";
        $query = $conn->executeQuery(
            $sql,
            ['quarter_id' => $quarterId, 'field_office_id' => $fieldOfficeId]
        );

        return $query->fetchAllAssociative();
    }
}
