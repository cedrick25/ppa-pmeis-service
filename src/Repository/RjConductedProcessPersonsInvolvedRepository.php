<?php

namespace App\Repository;

use App\Entity\RjConductedProcessPersonsInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RjConductedProcessPersonsInvolved>
 *
 * @method RjConductedProcessPersonsInvolved|null find($id, $lockMode = null, $lockVersion = null)
 * @method RjConductedProcessPersonsInvolved|null findOneBy(array $criteria, array $orderBy = null)
 * @method RjConductedProcessPersonsInvolved[]    findAll()
 * @method RjConductedProcessPersonsInvolved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RjConductedProcessPersonsInvolvedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, RjConductedProcessPersonsInvolved::class);
    }

    public function batchCreate(int $rjConductedProcessId, array $personsInvolved): void
    {
        foreach ($personsInvolved as $personInvolved) {
            $type = $personInvolved['type']['value'];
            $id = 'others' === $type ? 0 : (int) $personInvolved['id']['value'];

            $entity = new RjConductedProcessPersonsInvolved();
            $entity->setRjConductedProcessId($rjConductedProcessId);
            $entity->setPersonsInvolvedId($id);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($personInvolved['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(RjConductedProcessPersonsInvolved::class);
    }

    public function findByConductedProcessIds(array $ids): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rcppi.rj_conducted_process_id, rcppi.persons_involved_id, rcppi.type, rcppi.others_name
                 FROM rj_conducted_process_persons_involved as rcppi WHERE rcppi.rj_conducted_process_id IN (:ids)",
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );

        $results = $query->fetchAllAssociative();

        if (\count($results) === 0) {
            return [];
        }

        $return = [];
        $users = $this->getUserNames();
        $volunteers = $this->getVolunteerNames();

        foreach ($results as $result) {
            $personsInvolvedId = $result['persons_involved_id'];

            ['type' => $type, 'name' => $name] = $this->convertData($result['type'], $personsInvolvedId, $users, $volunteers, $result['others_name']);

            $return[$result['rj_conducted_process_id']][] = [
                'type' => $type,
                'id' => 'others' !== $result['type'] ?
                    [
                        'label' => $name,
                        'value' => $personsInvolvedId
                    ] : null,
                'othersName' => 'others' !== $result['type'] ? '' : $name,
            ];
        }

        return $return;
    }

    public function findByConductedProcessId(int $id): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rcppi.rj_conducted_process_id, rcppi.persons_involved_id, rcppi.type, rcppi.others_name
                    FROM rj_conducted_process_persons_involved as rcppi WHERE rcppi.rj_conducted_process_id = :id",
            ['id' => $id]
        );

        $results = $query->fetchAllAssociative();

        if (\count($results) === 0) {
            return [];
        }

        $return = [];
        $users = $this->getUserNames();
        $volunteers = $this->getVolunteerNames();

        foreach ($results as $result) {
            $personsInvolvedId = $result['persons_involved_id'];

            ['type' => $type, 'name' => $name] = $this->convertData($result['type'], $personsInvolvedId, $users, $volunteers, $result['others_name']);

            $return[$result['rj_conducted_process_id']][] = [
                'type' => $type,
                'id' => 'others' !== $result['type'] ?
                    [
                        'label' => $name,
                        'value' => $personsInvolvedId
                    ] : null,
                'othersName' => 'others' !== $result['type'] ? '' : $name,
            ];
        }

        return $return;
    }

    public function getVolunteerIdsByConductedProcessIds(array $conductedProcessIds): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rcppi.persons_involved_id FROM rj_conducted_process_persons_involved as rcppi
                    WHERE rcppi.rj_conducted_process_id IN (:ids)
                    AND rcppi.type = 'VPA' ",
            ['ids' => $conductedProcessIds],
            ['ids' => Connection::PARAM_INT_ARRAY],
        );

        return $query->fetchAllAssociative();
    }

    public function getDistinctConductedProcessIdsByVolunteerIds(array $volunteerIds): array
    {
        return $this->createQueryBuilder('rjcppi')
            ->select('rjcppi.rjConductedProcessId')
            ->where("rjcppi.type = 'VPA'")
            ->andWhere('rjcppi.personsInvolvedId IN (:ids)')
            ->setParameter('ids', $volunteerIds, Connection::PARAM_INT_ARRAY)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    private function convertData(
        string $type,
        int $personsInvolvedId,
        array $users,
        array $volunteers,
        ?string $othersName = ''
    ): array {
        switch ($type) {
            case 'PPO':
                $name = $users[$personsInvolvedId];
                $type = [
                    'label' => 'PPO (Parole Probation Officer)',
                    'value' => 'PPO'
                ];
                break;
            case 'VPA':
                $name = $volunteers[$personsInvolvedId];
                $type = [
                    'label' => 'VPA (Volunteer Probation Assistant)',
                    'value' => 'VPA'
                ];
                break;
            default:
                $name = $othersName;
                $type = [
                    'label' => 'Others',
                    'value' => 'others'
                ];
                break;
        }

        return [
            'type' => $type,
            'name' => $name
        ];
    }

    public function deleteByConductedProcessId(int $id): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            "DELETE FROM rj_conducted_process_persons_involved as rcppi WHERE rcppi.rj_conducted_process_id = :id",
            ['id' => $id]
        );
    }

    public function getDistinctPersonResponsibleByConductedProcessIds(array $ids, string $type): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT DISTINCT(rcppi.persons_involved_id) FROM rj_conducted_process_persons_involved as rcppi
                    WHERE rcppi.rj_conducted_process_id IN (:ids) AND rcppi.type = :type",
            ['ids' => $ids, 'type' => $type],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );

        return $query->fetchAllAssociative();
    }

    /**
     * @return array<string, string>
     */
    private function getUserNames(): array
    {
        $data = [];

        $userDetails = $this->userDetailsRepository->findAll();
        foreach ($userDetails as $userDetail) {
            $data[$userDetail->getUserAccountId()] = $userDetail->getFirstName() . ' ' . $userDetail->getMiddleName() . ' ' . $userDetail->getLastName();
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function getVolunteerNames(): array
    {
        $data = [];

        $volunteers = $this->volunteerRepository->findAll();
        foreach ($volunteers as $volunteer) {
            $data[$volunteer->getVolunteerId()] = $volunteer->getFirstName() . ' ' . $volunteer->getMiddleName() . ' ' . $volunteer->getLastName();
        }

        return $data;
    }
}
