<?php

namespace App\Repository;

use App\Entity\RjConductedProcessClients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RjConductedProcessClients>
 *
 * @method RjConductedProcessClients|null find($id, $lockMode = null, $lockVersion = null)
 * @method RjConductedProcessClients|null findOneBy(array $criteria, array $orderBy = null)
 * @method RjConductedProcessClients[]    findAll()
 * @method RjConductedProcessClients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RjConductedProcessClientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RjConductedProcessClients::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function bulkCreate(int $rjConductProcessId, array $clientIds): void
    {
        foreach ($clientIds as $clientId) {
            $entity = new RjConductedProcessClients();
            $entity->setClientId($clientId);
            $entity->setRjConductProcessId($rjConductProcessId);

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    public function deleteByConductedProcessId(int $id): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            "DELETE FROM rj_conducted_process_clients as rcpc WHERE rcpc.rj_conducted_process_id = :id",
            ['id' => $id]
        );
    }

    public function findByConductedProcessIds(array $ids): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rcppi.rj_conducted_process_id, rcppi.persons_involved_id, rcppi.type, rcppi.others_name
                 FROM rj_conducted_process_clients as rcpc WHERE rcppi.rj_conducted_process_id IN (:ids)",
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );

        return $query->fetchAllAssociative();
    }

    public function findClientsWithDetailsByConductProcessId(array $ids): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rcpc.rj_conduct_process_id, c.client_id, c.first_name, c.middle_name, c.last_name, c.gender
                    FROM rj_conducted_process_clients rcpc
                    LEFT JOIN clients c on rcpc.client_id = c.client_id
                    WHERE rcpc.rj_conduct_process_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $conductProcessId = (int) $result['rj_conduct_process_id'];
            if (! isset($return[$conductProcessId])) {
                $return[$conductProcessId][] = $result;

                continue;
            }

            $return[$conductProcessId][] = $result;
        }

        return $return;
    }
}
