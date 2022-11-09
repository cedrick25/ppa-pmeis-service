<?php

namespace App\Repository;

use App\Entity\CapabilityBuildingParticipants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CapabilityBuildingParticipants>
 *
 * @method CapabilityBuildingParticipants|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapabilityBuildingParticipants|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapabilityBuildingParticipants[]    findAll()
 * @method CapabilityBuildingParticipants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapabilityBuildingParticipantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CapabilityBuildingParticipants::class);
    }

    public function batchCreate(int $capabilityBuildingId, array $participants): void
    {
        foreach ($participants as $participant) {
            $entity = new CapabilityBuildingParticipants();
            $entity->setCapabilityBuildingId($capabilityBuildingId);
            $entity->setPersonnelName($participant['id']['label']);
            $entity->setPersonnelId($participant['id']['value']);
            $entity->setRemarks($participant['remarks']);

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(CapabilityBuildingParticipants::class);
    }

    public function deleteByCapabilityBuildingId(int $capabilityBuildingId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM capability_building_participants WHERE capability_building_id = :capability_building_id",
                ['capability_building_id' => $capabilityBuildingId]
            );
    }

    public function findParticipantsByCapabilityBuildingsId(array $ids): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT cbp.* FROM capability_building_participants cbp
                    WHERE cbp.capability_building_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $capabilityBuildingId = (int) $result['capability_building_id'];
            if (! isset($return[$capabilityBuildingId])) {
                $return[$capabilityBuildingId][] = $result;

                continue;
            }

            $return[$capabilityBuildingId][] = $result;
        }

        return $return;
    }
}
