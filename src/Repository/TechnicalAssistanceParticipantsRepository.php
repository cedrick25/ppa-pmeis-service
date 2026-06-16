<?php

namespace App\Repository;

use App\Entity\TechnicalAssistanceParticipants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TechnicalAssistanceParticipants>
 *
 * @method TechnicalAssistanceParticipants|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalAssistanceParticipants|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalAssistanceParticipants[]    findAll()
 * @method TechnicalAssistanceParticipants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalAssistanceParticipantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TechnicalAssistanceParticipants::class);
    }

    public function batchCreate(int $technicalAssistanceId, array $participants): void
    {
        foreach ($participants as $participant) {
            $entity = new TechnicalAssistanceParticipants();
            $entity->setTechnicalAssistanceId($technicalAssistanceId);
            $entity->setNo($participant['no']);
            $entity->setType($participant['type']);

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(TechnicalAssistanceParticipants::class);
    }

    public function deleteByTechnicalAssistanceId(int $technicalAssistanceId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM technical_assistance_participants
                        WHERE technical_assistance_id = :technical_assistance_id",
                ['technical_assistance_id' => $technicalAssistanceId],
            );
    }

    public function findByTechnicalAssistanceId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT tap.* FROM technical_assistance_participants tap
                    WHERE tap.technical_assistance_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        if (empty($results)) {
            return [];
        }

        $response = [];

        foreach ($results as $result) {
            $response[$result['technical_assistance_id']][] = [
                'no' => $result['no'],
                'type' => $result['type'],
            ];
        }

        return $response;
    }
}
