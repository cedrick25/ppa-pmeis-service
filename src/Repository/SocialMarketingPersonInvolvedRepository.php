<?php

namespace App\Repository;

use App\Entity\SocialMarketingPersonInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialMarketingPersonInvolved>
 *
 * @method SocialMarketingPersonInvolved|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketingPersonInvolved|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketingPersonInvolved[]    findAll()
 * @method SocialMarketingPersonInvolved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingPersonInvolvedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, SocialMarketingPersonInvolved::class);
    }

    public function batchCreate(int $socialMarketingId, array $personsInvolved): void
    {
        foreach ($personsInvolved as $personInvolved) {
            $type = $personInvolved['type']['value'];
            $id = 'others' === $type ? 0 : (int) $personInvolved['id']['value'];

            $entity = new SocialMarketingPersonInvolved();
            $entity->setSocialMarketingId($socialMarketingId);
            $entity->setPersonInvolvedId($id);
            $entity->setType($type);
            $entity->setRole($personInvolved['role']);

            if ('others' === $type) {
                $entity->setOthersName($personInvolved['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(SocialMarketingPersonInvolved::class);
    }

    public function deleteBySocialMarketingId(int $socialMarketingId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM social_marketing_person_involved WHERE social_marketing_id = :social_marketing_id",
                ['social_marketing_id' => $socialMarketingId],
            );
    }

    public function findBySocialMarketingsId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT smpi.* FROM social_marketing_person_involved smpi
                    WHERE smpi.social_marketing_id IN (:ids)",
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
            $personsInvolvedId = $result['person_involved_id'];

            ['type' => $type, 'name' => $name, 'role' => $role] = $this->convertData(
                $result['type'],
                $result['role'],
                $personsInvolvedId,
                $users,
                $volunteers,
                $result['others_name']
            );

            $return[$result['social_marketing_id']][] = [
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
            'role' => $role,
            'name' => $name
        ];
    }
}
