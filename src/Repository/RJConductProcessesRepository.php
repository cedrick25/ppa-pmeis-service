<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Enum\Response as ResponseEnum;
use App\Model\RJConductProcesses as RJConductProcessesModel;
use App\Entity\RJConductProcesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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
    ) {
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

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('p')
                ->where('p.deletedAt IS NULL')
                ->orderBy('p.rjConductProcessId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws Exception
     */
    public function create(RJConductProcessesModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newRJConductProcesses = new RJConductProcesses();

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
        $newRJConductProcesses->setCreatedBy($data->getCreatedBy());
        $newRJConductProcesses->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newRJConductProcesses);
        $this->getEntityManager()->flush();

        return $newRJConductProcesses->getRJConductProcessId();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $entity = $this->isExistingById($id);

        if (! $entity) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $entity =$this->isExistingById($id);

        if ($entity == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | RJConductProcesses
    {
        $entity = $this->findOneBy([
            'rjConductProcessId' => $id,
            'deletedAt' => null
        ]);

        return ($entity == null) ? false : $entity;
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
            $sql = "SELECT rjcp.*, o.name as offense, rjcp.pe_date, (SELECT name FROM venues WHERE venues.venue_id = rjcp.pe_venue_id) as pe_venue,
                    rjcp.pe_activity, rjcp.pe_date, (SELECT name FROM venues WHERE venues.venue_id = rjcp.rjp_venue_id) as rjp_venue,
                    rjp.name as rjp_type, ud.first_name as planner_fn, ud.middle_name as planner_mn, ud.last_name as planner_ln,
                    rjps.name as rjp_status, ro.name as rj_outcome_name, ro.code as rj_outcome_code
                    FROM rjconduct_processes as rjcp 
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

        $entity->setQuarterId($data->getQuarterId());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setOffenseId($data->getOffenseId());
        $entity->setPeVenueId($data->getPeVenueId());
        $entity->setPeDate(
            $this->appDateHelper->convertStringToImmutableDate($data->getPeDate())
        );
        $entity->setPeActivity($data->getPeActivity());
        $entity->setRjpDate(
            $this->appDateHelper->convertStringToImmutableDate($data->getRjpDate())
        );
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

    public function findByFieldOfficesId(int $quarterId, array $fieldOfficesId): array
    {
        $response = [];
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT * FROM rjconduct_processes rp WHERE rp.field_office_id IN (:fieldOfficesId)
                        AND rp.quarter_id = :quarterId ",
                [
                    'fieldOfficesId' => $fieldOfficesId,
                    'quarterId' => $quarterId,
                ],
                ['fieldOfficesId' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $fieldOfficeId = $result['field_office_id'];

            if (! isset($response[$fieldOfficeId])) {
                $response[$fieldOfficeId] = [];
            }

            $response[$fieldOfficeId][] = $result;
        }

        return $response;
    }

    public function findByFieldOfficesIdWithDetails(int $quarterId, array $fieldOfficesId): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rp.*, rs.name as status, ro.name as outcome FROM rjconduct_processes as rp
                    LEFT JOIN rjprocess_status as rs ON rp.rjps_id = rs.id_rjprocess_status
                    LEFT JOIN rjoutcomes as ro ON rp.rjo_id = ro.rj_outcome_id
                    WHERE rp.field_office_id IN (:fieldOfficesId) AND rp.quarter_id = :quarterId",
                [
                    'fieldOfficesId' => $fieldOfficesId,
                    'quarterId' => $quarterId,
                ],
                ['fieldOfficesId' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
    }

    public function findByQuarterAndFieldOffice(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rp.rj_conduct_process_id FROM rjconduct_processes as rp
                WHERE rp.quarter_id = :quarter_id AND rp.field_office_id = :field_office_id
                  AND rp.deleted_at IS NULL";
        $query = $conn->executeQuery(
            $sql,
            ['quarter_id' => $quarterId, 'field_office_id' => $fieldOfficeId]
        );

        return $query->fetchAllAssociative();
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param int $fieldOfficeId
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10, int $filedOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getClientsPaginatedKey($page, $pageSize),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use ($pageSize, $page, $filedOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT rp.*, rs.name as status, ro.name as outcome, CONCAT(ud.first_name, ' ', ud.last_name) as created_by FROM rjconduct_processes as rp
                        LEFT JOIN rjprocess_status as rs ON rp.rjps_id = rs.id_rjprocess_status
                        LEFT JOIN rjoutcomes as ro ON rp.rjo_id = ro.rj_outcome_id
                        LEFT JOIN user_details as ud ON rp.created_by = ud.user_account_id ";
            
            if ($filedOfficeId > 0) {
                $sql .= "WHERE rp.field_office_id = $filedOfficeId ";
            }

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "ORDER BY rp.rj_conduct_process_id DESC LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }
}
