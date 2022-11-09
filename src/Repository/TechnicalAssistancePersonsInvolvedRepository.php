<?php

namespace App\Repository;

use App\Entity\TechnicalAssistancePersonsInvolved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TechnicalAssistancePersonsInvolved::class);
    }


    public function batchCreate(int $technicalAssistanceId, array $personsInvolved): void
    {
        foreach ($personsInvolved as $personInvolved) {
            $type = $personInvolved['type']['value'];
            $id = 'others' === $type ? 0 : (int) $personInvolved['id']['value'];

            $entity = new TechnicalAssistancePersonsInvolved();
            $entity->setTechnicalAssistanceId($technicalAssistanceId);
            $entity->setPersonsInvolvedId($id);
            $entity->setType($type);

            if ('others' === $type) {
                $entity->setOthersName($personInvolved['othersName']);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(TechnicalAssistancePersonsInvolved::class);
    }
}
