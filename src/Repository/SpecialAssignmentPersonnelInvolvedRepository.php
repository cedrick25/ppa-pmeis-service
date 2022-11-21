<?php

namespace App\Repository;

use App\Entity\SpecialAssignmentPersonnelInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpecialAssignmentPersonnelInvolved>
 *
 * @method SpecialAssignmentPersonnelInvolved|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialAssignmentPersonnelInvolved|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialAssignmentPersonnelInvolved[]    findAll()
 * @method SpecialAssignmentPersonnelInvolved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialAssignmentPersonnelInvolvedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, SpecialAssignmentPersonnelInvolved::class);
    }

    public function batchCreate(int $id, array $personsResponsible): void
    {
        foreach ($personsResponsible as $personResponsible) {
            $type = $personResponsible['type']['value'];
            $personnelInvolvedId = 'others' === $type ? 0 : (int) $personResponsible['id']['value'];

            $entity = new SpecialAssignmentPersonnelInvolved();
            $entity->setSpecialAssignmentId($id);
            $entity->setPersonnelInvolvedId($personnelInvolvedId);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($personResponsible['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(SpecialAssignmentPersonnelInvolved::class);
    }

    public function deleteBySpecialAssignmentId(int $id): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM special_assignment_personnel_involved
                     WHERE special_assignment_id = :special_assignment_id",
                ['special_assignment_id' => $id],
            );
    }

    public function findBySpecialAssignmentId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT sapi.* FROM special_assignment_personnel_involved sapi
                    WHERE sapi.special_assignment_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        if (empty($results)) {
            return [];
        }

        $return = [];
        $users = $this->getUserNames();
        $volunteers = $this->getVolunteerNames();

        foreach ($results as $result) {
            $personnelInvolvedId = (int) $result['personnel_involved_id'];

            ['type' => $type, 'name' => $name] = $this->convertData(
                $result['type'],
                $personnelInvolvedId,
                $users,
                $volunteers,
                $result['others_name']
            );

            $return[$result['special_assignment_id']][] = [
                'type' => $type,
                'id' => 'others' !== $result['type'] ?
                    [
                        'label' => $name,
                        'value' => $personnelInvolvedId
                    ] : null,
                'othersName' => 'others' !== $result['type'] ? '' : $name,
            ];
        }

        return $return;
    }

    /**
     * @return array<string, string>
     */
    private function getUserNames(): array
    {
        $data = [];

        $userDetails = $this->userDetailsRepository->findAll();
        foreach ($userDetails as $userDetail) {
            $data[$userDetail->getUserAccountId()] = $userDetail->getFirstName()
                .' '
                . $userDetail->getMiddleName()
                . ' '
                . $userDetail->getLastName();
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
            $data[$volunteer->getVolunteerId()] = $volunteer->getFirstName()
                . ' '
                . $volunteer->getMiddleName()
                . ' '
                . $volunteer->getLastName();
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
