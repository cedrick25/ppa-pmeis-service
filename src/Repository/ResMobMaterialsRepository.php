<?php

namespace App\Repository;

use App\Entity\ResMobMaterials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResMobMaterials>
 *
 * @method ResMobMaterials|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResMobMaterials|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResMobMaterials[]    findAll()
 * @method ResMobMaterials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResMobMaterialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResMobMaterials::class);
    }

    public function batchCreate(int $id, array $materials): void
    {
        foreach ($materials as $material) {
            $entity = new ResMobMaterials();
            $entity->setResMobId($id);
            $entity->setEstimatedAmount($material['estimatedAmount']);
            $entity->setParticularQuantity($material['particularQuantity']);
            $entity->setParticularType($material['particularType']);
            $entity->setSourceName($material['sourceName']);
            $entity->setSourceType($material['sourceType']['value']);

            $this->_em->persist($entity);
        }

        $this->_em->flush();
        $this->_em->clear(ResMobMaterials::class);
    }

    public function findByResMobsId(array $resMobsId): array
    {
        $return = [];

        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT rmm.* FROM res_mob_materials rmm WHERE rmm.res_mob_id IN (:ids)",
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
                "DELETE FROM res_mob_materials WHERE res_mob_id = :res_mob_id",
                ['res_mob_id' => $resMobId]
            );
    }
}
