<?php

namespace App\Repository;

use App\Entity\RjRelatedActivitiesPersonsInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RjRelatedActivitiesPersonsInvolved|null find($id, $lockMode = null, $lockVersion = null)
 * @method RjRelatedActivitiesPersonsInvolved|null findOneBy(array $criteria, array $orderBy = null)
 * @method RjRelatedActivitiesPersonsInvolved[]    findAll()
 * @method RjRelatedActivitiesPersonsInvolved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
    class RjRelatedActivitiesPersonsInvolvedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, RjRelatedActivitiesPersonsInvolved::class);
    }

    /**
     * @param int $rjRelatedActivitiesId
     * @param array<string, mixed> $personsInvolved
     */
    public function batchCreate(int $rjRelatedActivitiesId, array $personsInvolved): void
    {
        foreach ($personsInvolved as $personInvolved) {
            $type = $personInvolved['type']['value'];
            $id = 'others' === $type ? 0 : (int) $personInvolved['id']['value'];

            $entity = new RjRelatedActivitiesPersonsInvolved();
            $entity->setRelatedActivityId($rjRelatedActivitiesId);
            $entity->setPersonsInvolvedId($id);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($personInvolved['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(RjRelatedActivitiesPersonsInvolved::class);
    }

    public function findByConductedProcessIds(array $ids): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rrapi.related_activity_id, rrapi.persons_involved_id, rrapi.type, rrapi.others_name FROM rj_related_activities_persons_involved as rrapi WHERE rrapi.related_activity_id IN (:ids)",
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

            $return[$result['related_activity_id']][] = [
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

    public function deleteByRelatedActivityId(int $id): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            "DELETE FROM rj_related_activities_persons_involved as rrapi WHERE rrapi.related_activity_id = :id",
            ['id' => $id]
        );
    }

    public function findByRelatedActivityId(int $id): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT rrapi.related_activity_id, rrapi.persons_involved_id, rrapi.type, rrapi.others_name FROM rj_related_activities_persons_involved as rrapi WHERE rrapi.related_activity_id = :id",
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

            $return[$result['related_activity_id']][] = [
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

    /**
     * @param int $relatedActivityId
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteersByRelatedActivityId(int $relatedActivityId): array
    {
//        $conn = $this->getEntityManager()->getConnection();
//        $sql = "SELECT v.first_name, v.middle_name, v.last_name FROM rj_related_activities_persons_involved
//                LEFT JOIN volunteer v on rjvolunteers.volunteer_id = v.volunteer_id
//                WHERE rjvolunteers.related_activity_id = $relatedActivityId";
//        $stmt = $conn->prepare($sql);
//        $query = $stmt->executeQuery();

//        return $query->fetchAllAssociative();
        return [];
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
}
