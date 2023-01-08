<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\VolunteerOperations;
use App\Entity\VolunteerSupervisions;
use App\Model\VolunteerSupervisions as VolunteerSupervisionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method VolunteerSupervisions|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerSupervisions|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerSupervisions[]    findAll()
 * @method VolunteerSupervisions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerSupervisionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "volunteer_supervisions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
        private VolunteerSupervisionClientsRepository $supervisionClientsRepository,
    ) {
        parent::__construct($registry, VolunteerSupervisions::class);
    }

    /**
     * @return VolunteerSupervisions[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVolunteerSupervisionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('vs')
                ->orderBy('vs.volunteerSupervisionsId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(VolunteerSupervisionsModel $data): int
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new VolunteerSupervisions();
        $entity->setVolunteerId($data->getVolunteerId());
        $entity->setServicesRenderedId($data->getServicesRenderedId());
        $entity->setCommunityResourcesTapped($data->getCommunityResourcesTapped());
        $entity->setAssistanceReceived($data->getAssistanceReceived());
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setQuarterId($data->getQuarterId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getVolunteerSupervisionsId();

        $this->supervisionClientsRepository->bulkCreate($id, $data->getClientIds());

        return $id;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $volunteerSupervision = $this->isExistingById($id);
        if (! $volunteerSupervision) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($volunteerSupervision);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | VolunteerSupervisions
    {
        $volunteerSupervision = $this->findOneBy([
            'volunteerSupervisionsId' => $id
        ]);

        return ($volunteerSupervision == null) ? false : $volunteerSupervision;
    }

    public function getById(int $id): array|bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT vs.*, v.last_name, v.first_name, v.middle_name, fo.name field_office, r.name region,
                        r.region_id as region_id, q.year
                    FROM volunteer_supervisions as vs
                    LEFT JOIN volunteer v on vs.volunteer_id = v.volunteer_id
                    LEFT JOIN field_offices fo on vs.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    LEFT JOIN quarters q on vs.quarter_id = q.quarter_id
                    WHERE vs.volunteer_supervisions_id = :volunteer_supervisions_id AND vs.deleted_at IS NULL",
                ['volunteer_supervisions_id' => $id]
            )->fetchAssociative();
    }

    /**
     * @throws Exception
     */
    public function findByQuarter(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT vs.*, v.first_name v_firstname, v.middle_name v_middlename, v.last_name v_lastname,
                v.gender v_gender, c.first_name c_firstname, c.middle_name c_middlename, c.last_name c_lastname,
                c.gender  c_gender, sr.name service_rendered
                FROM volunteer_supervisions as vs
                LEFT JOIN volunteer v on vs.volunteer_id = v.volunteer_id
                LEFT JOIN volunteer_supervision_clients vsc on
                    vs.volunteer_supervisions_id = vsc.volunteer_supervision_id
                LEFT JOIN clients c on vsc.client_id = c.client_id
                LEFT JOIN services_rendered sr on vs.services_rendered_id = sr.services_rendered_id
                WHERE vs.quarter_id = $quarterId AND vs.field_office_id = $fieldOfficeId";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param array $ids
     * @return array
     * @throws Exception
     */
    public function findByVolunteerIds(array $ids): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT * FROM volunteer_supervisions
                WHERE volunteer_id IN (:ids)";
        $query = $conn->executeQuery($sql, ['ids' => $ids], ['ids' => Connection::PARAM_INT_ARRAY]);

        return $query->fetchAllAssociative();
    }

    public function getServicesRenderedByVolunteersId(int $quarterId, array $volunteerIds): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT vs.services_rendered_id FROM volunteer_supervisions vs
                    WHERE vs.volunteer_id IN (:volunteerIds)
                    AND vs.quarter_id = :quarterId",
                [
                    'quarterId' => $quarterId,
                    'volunteerIds' => $volunteerIds
                ],
                ['volunteerIds' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
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
            'cacheKey' => $this->cacheHelper->getVolunteerSupervisionPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT vs.*, v.last_name, v.first_name, v.middle_name, fo.name field_office, r.name region,
                        r.region_id as region_id
                    FROM volunteer_supervisions as vs
                    LEFT JOIN volunteer v on vs.volunteer_id = v.volunteer_id
                    LEFT JOIN field_offices fo on vs.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE vs.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }
}
