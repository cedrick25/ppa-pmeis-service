<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\RJConductProcesses;
use App\Entity\VpaAssociationInitiatedActivities;
use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method VpaAssociationInitiatedActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpaAssociationInitiatedActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpaAssociationInitiatedActivities[]    findAll()
 * @method VpaAssociationInitiatedActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpaAssociationInitiatedActivitiesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "vpa_association_initiated_activities";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, VpaAssociationInitiatedActivities::class);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(VpaAssociationInitiatedActivitiesModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newVpaAssociationInitiatedActivities = new VpaAssociationInitiatedActivities();
        $newVpaAssociationInitiatedActivities->setServiceRenderedId($data->getServicesRenderedId());
        $newVpaAssociationInitiatedActivities->setVenueDate($this->appDateHelper->convertStringToImmutableDate($data->getVenueDate()));
        $newVpaAssociationInitiatedActivities->setVenueId($data->getVenueId());
        $newVpaAssociationInitiatedActivities->setVolunteerId($data->getVolunteerId());
        $newVpaAssociationInitiatedActivities->setRole($data->getRole());
        $newVpaAssociationInitiatedActivities->setCrdResourcesTapped($data->getCrdResourcesTapped());
        $newVpaAssociationInitiatedActivities->setCrdAssistanceReceived($data->getCrdAssistanceReceived());
        $newVpaAssociationInitiatedActivities->setRemarks($data->getRemarks());
        $newVpaAssociationInitiatedActivities->setFieldOfficeId($data->getFieldOfficeId());
        $newVpaAssociationInitiatedActivities->setQuarterId($data->getQuarterId());
        $newVpaAssociationInitiatedActivities->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());
        $newVpaAssociationInitiatedActivities->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newVpaAssociationInitiatedActivities);
        $this->getEntityManager()->flush();

        return $newVpaAssociationInitiatedActivities->getVpaAssociationInitiatedActivityId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $vpaAssociationInitiatedActivities = $this->isExistingById($id);
        if (! $vpaAssociationInitiatedActivities) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($vpaAssociationInitiatedActivities);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | VpaAssociationInitiatedActivities
    {
        $vpaAssociationInitiatedActivities = $this->findOneBy([
            'vpaAssociationInitiatedActivityId' => $id,
            'deletedAt' => null
        ]);

        return ($vpaAssociationInitiatedActivities == null) ? false : $vpaAssociationInitiatedActivities;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT sr.name service_rendered, vaia.venue_date, v.name as venue, v2.first_name, v2.middle_name, v2.last_name, v2.gender,
                vaia.role, vaia.crd_resources_tapped, vaia.crd_assistance_received, vaia.remarks FROM vpa_association_initiated_activities vaia
                LEFT JOIN services_rendered sr on vaia.service_rendered_id = sr.services_rendered_id
                LEFT JOIN venues v on vaia.venue_id = v.venue_id
                LEFT JOIN volunteer v2 on vaia.volunteer_id = v2.volunteer_id
                WHERE vaia.field_office_id = $fieldOfficeId 
                AND vaia.quarter_id = $quarterId 
                AND vaia.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

        /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getVolunteerIdsByDateRange(int $quarterId, int $fieldOfficeId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT 
                    DISTINCT v.volunteer_id
                    FROM vpa_association_initiated_activities vaia
                LEFT JOIN volunteer as v ON v.volunteer_id = vaia.volunteer_id
                WHERE vaia.quarter_id = $quarterId 
                AND vaia.field_office_id = $fieldOfficeId
                AND vaia.deleted_at IS NULL
                ";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
