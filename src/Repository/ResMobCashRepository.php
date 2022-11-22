<?php

namespace App\Repository;

use App\Entity\ResMobCash;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResMobCash>
 *
 * @method ResMobCash|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResMobCash|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResMobCash[]    findAll()
 * @method ResMobCash[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResMobCashRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResMobCash::class);
    }

    public function batchCreate(int $id, array $cashes): void
    {
        foreach ($cashes as $cash) {
            $entity = new ResMobCash();
            $entity->setResMobId($id);
            $entity->setAmount($cash['amount']);
            $entity->setSourceName($cash['sourceName']);
            $entity->setSourceType($cash['sourceType']['value']);

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(ResMobCash::class);
    }

    public function findByResMobsId(array $resMobsId): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rmc.* FROM res_mob_cash rmc WHERE rmc.res_mob_id IN (:ids)",
                ['ids' => $resMobsId],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $resMobId = (int) $result['res_mob_id'];
            $result['source_type'] = [
                'label' =>  $result['source_type'],
                'value' =>  $result['source_type'],
            ];

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
                "DELETE FROM res_mob_cash WHERE res_mob_id = :res_mob_id",
                ['res_mob_id' => $resMobId]
            );
    }
}
