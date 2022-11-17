<?php

namespace App\Repository;

use App\Entity\ResMobTechnicalAssistance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResMobTechnicalAssistance>
 *
 * @method ResMobTechnicalAssistance|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResMobTechnicalAssistance|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResMobTechnicalAssistance[]    findAll()
 * @method ResMobTechnicalAssistance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResMobTechnicalAssistanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResMobTechnicalAssistance::class);
    }

    public function batchCreate(int $id, array $technicalAssistances): void
    {
        foreach ($technicalAssistances as $technicalAssistance) {
            $entity = new ResMobTechnicalAssistance();
            $entity->setResMobId($id);
            $entity->setEstimatedAmount($technicalAssistance['estimatedAmount']);
            $entity->setParticularQuantity($technicalAssistance['particularQuantity']);
            $entity->setParticularType($technicalAssistance['particularType']);
            $entity->setSourceName($technicalAssistance['sourceName']);
            $entity->setSourceType($technicalAssistance['sourceType']);

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(ResMobTechnicalAssistance::class);
    }

    public function findByResMobsId(array $resMobsId): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rmta.* FROM res_mob_technical_assistance rmta WHERE rmta.res_mob_id IN (:ids)",
                ['ids' => $resMobsId],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $resMobId = (int) $result['res_mob_id'];

            if (! isset($return[$resMobId])) {
                $return[$resMobId][] = $result;

                continue;
            }

            $return[$resMobId][] = $result;
        }

        return $return;
    }

    public function deleteByResMobId(int $resMobId): void
    {
        $this->getEntityManager()->getConnection()
            ->executeQuery(
                "DELETE FROM res_mob_technical_assistance WHERE res_mob_id = :res_mob_id",
                ['res_mob_id' => $resMobId]
            );
    }
}
