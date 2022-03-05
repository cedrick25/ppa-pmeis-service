<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\VolunteerOperations;
use App\Entity\VolunteerSupervisions;
use App\Model\VolunteerSupervisions as VolunteerSupervisionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('vs')
                ->orderBy('vs.volunteerSupervisionsId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws \Exception
     */
    public function bulkCreate(VolunteerSupervisionsModel $data): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        foreach ($data->getClientIds() as $clientId) {
            $newVolunteerSupervisions = new VolunteerSupervisions();
            $newVolunteerSupervisions->setVolunteerId($data->getVolunteerId());
            $newVolunteerSupervisions->setClientId($clientId);
            $newVolunteerSupervisions->setServicesRenderedId($data->getServicesRenderedId());
            $newVolunteerSupervisions->setCommunityResourcesTapped($data->getCommunityResourcesTapped());
            $newVolunteerSupervisions->setAssistanceReceived($data->getAssistanceReceived());
            $newVolunteerSupervisions->setRemarks($data->getRemarks());
            $newVolunteerSupervisions->setFieldOfficeId($data->getFieldOfficeId());
            $newVolunteerSupervisions->setQuarterId($data->getQuarterId());
            $newVolunteerSupervisions->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

            $this->getEntityManager()->persist($newVolunteerSupervisions);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
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

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByQuarter(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT vs.*, v.first_name v_firstname, v.middle_name v_middlename, v.last_name v_lastname,
                v.gender v_gender, c.first_name c_firstname, c.middle_name c_middlename, c.last_name c_lastname,
                c.gender  c_gender, sr.name service_rendered
                FROM volunteer_supervisions as vs
                LEFT JOIN volunteer v on vs.volunteer_id = v.volunteer_id
                LEFT JOIN clients c on vs.client_id = c.client_id
                LEFT JOIN services_rendered sr on vs.services_rendered_id = sr.services_rendered_id
                WHERE vs.quarter_id = $quarterId AND vs.field_office_id = $fieldOfficeId";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
