<?php

namespace App\Repository;

use App\Entity\ResMobSecuredBy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResMobSecuredBy>
 *
 * @method ResMobSecuredBy|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResMobSecuredBy|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResMobSecuredBy[]    findAll()
 * @method ResMobSecuredBy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResMobSecuredByRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, ResMobSecuredBy::class);
    }

    public function batchCreate(int $id, array $securedByList): void
    {
        foreach ($securedByList as $securedBy) {
            $type = $securedBy['type']['value'];
            $securedById = 'others' === $type ? 0 : (int) $securedBy['id']['value'];

            $entity = new ResMobSecuredBy();
            $entity->setResMobId($id);
            $entity->setSecuredById($securedById);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($securedBy['othersName']);
            }

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(ResMobSecuredBy::class);
    }

    public function findByResMobId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rmsb.* FROM res_mob_secured_by rmsb WHERE rmsb.res_mob_id IN (:ids)",
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
            $resMobId = (int) $result['res_mob_id'];

            ['type' => $type, 'name' => $name] = $this->convertData(
                $result['type'],
                $result['secured_by_id'],
                $users,
                $volunteers,
                $result['others_name']
            );

            $return[$result['res_mob_id']][] = [
                'type' => $type,
                'id' => 'others' !== $result['type'] ?
                    [
                        'label' => $name,
                        'value' => $resMobId
                    ] : null,
                'othersName' => 'others' !== $result['type'] ? '' : $name,
            ];
        }

        return $return;
    }

    public function deleteByResMobId(int $resMobId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM res_mob_secured_by WHERE res_mob_id = :res_mob_id",
                ['res_mob_id' => $resMobId]
            );
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
