<?php

namespace App\Repository;

use App\Entity\RJVolunteers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RJVolunteers|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJVolunteers|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJVolunteers[]    findAll()
 * @method RJVolunteers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJVolunteersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RJVolunteers::class);
    }

    /**
     * @param int[] $volunteersId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    public function batchCreate(int $rjRelatedActivitiesId, array $volunteersId): void
    {
        foreach ($volunteersId as $volunteerId) {
            $rjVolunteer = new RJVolunteers();
            $rjVolunteer->setVolunteerId($volunteerId);
            $rjVolunteer->setRelatedActivityId($rjRelatedActivitiesId);

            $this->getEntityManager()->persist($rjVolunteer);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(RJVolunteers::class);
    }

    /**
     * @param int $relatedActivityId
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteersByRelatedActivityId(int $relatedActivityId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT v.first_name, v.middle_name, v.last_name FROM rjvolunteers 
                LEFT JOIN volunteer v on rjvolunteers.volunteer_id = v.volunteer_id
                WHERE rjvolunteers.related_activity_id = $relatedActivityId";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
