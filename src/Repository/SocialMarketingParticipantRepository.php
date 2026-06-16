<?php

namespace App\Repository;

use App\Entity\SocialMarketingParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialMarketingParticipant>
 *
 * @method SocialMarketingParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketingParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketingParticipant[]    findAll()
 * @method SocialMarketingParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialMarketingParticipant::class);
    }

    public function batchCreate(int $socialMarketingId, array $participants): void
    {
        foreach ($participants as $participant) {
            $entity = new SocialMarketingParticipant();
            $entity->setSocialMarketingId($socialMarketingId);
            $entity->setType($participant['type']);
            $entity->setNo($participant['no']);

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(SocialMarketingParticipant::class);
    }

    public function deleteBySocialMarketingId(int $socialMarketingId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM social_marketing_participant
                        WHERE social_marketing_id = :social_marketing_id",
                ['social_marketing_id' => $socialMarketingId],
            );
    }

    public function findBySocialMarketingId(array $ids): array
    {
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT smp.* FROM social_marketing_participant smp
                    WHERE smp.social_marketing_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        if (empty($results)) {
            return [];
        }

        $response = [];

        foreach ($results as $result) {
            $response[$result['social_marketing_id']][] = [
                'no' => $result['no'],
                'type' => $result['type'],
            ];
        }

        return $response;
    }
}
