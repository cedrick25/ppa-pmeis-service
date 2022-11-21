<?php

namespace App\Repository;

use App\Entity\TechnicalAssistancePersonsInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TechnicalAssistancePersonsInvolved>
 *
 * @method TechnicalAssistancePersonsInvolved|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalAssistancePersonsInvolved|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalAssistancePersonsInvolved[]    findAll()
 * @method TechnicalAssistancePersonsInvolved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalAssistancePersonsInvolvedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, TechnicalAssistancePersonsInvolved::class);
    }

    public function batchCreate(int $technicalAssistanceId, array $personsInvolved): void
    {
        foreach ($personsInvolved as $personInvolved) {
            $type = $personInvolved['type']['value'];
            $role = $personInvolved['role']['value'];
            $id = 'others' === $type ? 0 : (int) $personInvolved['id']['value'];

            $entity = new TechnicalAssistancePersonsInvolved();
            $entity->setTechnicalAssistanceId($technicalAssistanceId);
            $entity->setPersonsInvolvedId($id);
            $entity->setType($type);
            $entity->setRole($role);

            if ('others' === $type) {
                $entity->setOthersName($personInvolved['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(TechnicalAssistancePersonsInvolved::class);
    }

    public function deleteByTechnicalAssistanceId(int $technicalAssistanceId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM technical_assistance_persons_involved
                        WHERE technical_assistance_id = :technical_assistance_id",
                ['technical_assistance_id' => $technicalAssistanceId],
            );
    }

    public function findPersonsInvolvedByTechnicalAssistanceId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT tapi.* FROM technical_assistance_persons_involved tapi
                    WHERE tapi.technical_assistance_id IN (:ids)",
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
            $personsInvolvedId = $result['persons_involved_id'];

            ['type' => $type, 'name' => $name, 'role' => $role] = $this->convertData(
                $result['type'],
                $result['role'],
                $personsInvolvedId,
                $users,
                $volunteers,
                $result['others_name']
            );

            $return[$result['technical_assistance_id']][] = [
                'type' => $type,
                'role' => $role,
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
        string $role,
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
            'role' => [
                'label' => $role,
                'value' => $role,
            ],
            'name' => $name
        ];
    }
}
