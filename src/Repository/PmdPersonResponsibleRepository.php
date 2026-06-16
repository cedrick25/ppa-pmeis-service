<?php

namespace App\Repository;

use App\Entity\PmdPersonResponsible;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PmdPersonResponsible>
 *
 * @method PmdPersonResponsible|null find($id, $lockMode = null, $lockVersion = null)
 * @method PmdPersonResponsible|null findOneBy(array $criteria, array $orderBy = null)
 * @method PmdPersonResponsible[]    findAll()
 * @method PmdPersonResponsible[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PmdPersonResponsibleRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, PmdPersonResponsible::class);
    }

    public function batchCreate(int $id, array $personsResponsible): void
    {
        foreach ($personsResponsible as $personResponsible) {
            $type = $personResponsible['type']['value'];
            $personResponsibleId = 'others' === $type ? 0 : (int) $personResponsible['id']['value'];

            $entity = new PmdPersonResponsible();
            $entity->setPmdId($id);
            $entity->setPersonResponsibleId($personResponsibleId);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($personResponsible['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(PmdPersonResponsible::class);
    }

    public function deleteByPmdId(int $pmdId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM pmd_person_responsible WHERE pmd_id = :pmd_id",
                ['pmd_id' => $pmdId],
            );
    }

    public function findPersonsResponsibleByPmdId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT ppr.* FROM pmd_person_responsible ppr
                    WHERE ppr.pmd_id IN (:ids)",
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
            $personsResponsibleId = (int) $result['person_responsible_id'];

            ['type' => $type, 'name' => $name] = $this->convertData(
                $result['type'],
                $personsResponsibleId,
                $users,
                $volunteers,
                $result['others_name']
            );

            $return[$result['pmd_id']][] = [
                'type' => $type,
                'id' => 'others' !== $result['type'] ?
                    [
                        'label' => $name,
                        'value' => $personsResponsibleId
                    ] : null,
                'othersName' => 'others' !== $result['type'] ? '' : $name,
            ];
        }

        return $return;
    }

    public function getDistinctPmdIdByVolunteerIds(array $volunteerIds): array
    {
        return $this->createQueryBuilder('pmdpr')
            ->select('pmdpr.pmdId')
            ->where("pmdpr.type = 'VPA'")
            ->andWhere('pmdpr.personResponsibleId IN (:ids)')
            ->setParameter('ids', $volunteerIds, Connection::PARAM_INT_ARRAY)
            ->distinct()
            ->getQuery()
            ->getResult();
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
