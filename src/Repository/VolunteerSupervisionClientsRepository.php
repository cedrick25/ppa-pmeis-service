<?php

namespace App\Repository;

use App\Entity\VolunteerSupervisionClients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Ds\Map;

/**
 * @extends ServiceEntityRepository<VolunteerSupervisionClients>
 *
 * @method VolunteerSupervisionClients|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerSupervisionClients|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerSupervisionClients[]    findAll()
 * @method VolunteerSupervisionClients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerSupervisionClientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VolunteerSupervisionClients::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VolunteerSupervisionClients $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(VolunteerSupervisionClients $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function bulkCreate(int $supervisionId, array $clientIds): void
    {
        foreach ($clientIds as $clientId) {
            $entity = new VolunteerSupervisionClients();
            $entity->setClientId($clientId);
            $entity->setVolunteerSupervisionId($supervisionId);

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    public function findClientsWithDetailsBySupervisionId(array $ids): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT vsc.volunteer_supervision_id, c.client_id, c.first_name, c.middle_name, c.last_name, c.gender
                    FROM volunteer_supervision_clients vsc
                    LEFT JOIN clients c on vsc.client_id = c.client_id
                    WHERE vsc.volunteer_supervision_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $volunteerSupervisionId = (int) $result['volunteer_supervision_id'];
            if (! isset($return[$volunteerSupervisionId])) {
                $return[$volunteerSupervisionId][] = $result;

                continue;
            }

            $return[$volunteerSupervisionId][] = $result;
        }

        return $return;
    }
}
