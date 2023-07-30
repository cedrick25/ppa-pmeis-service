<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Entity\TclpComputationForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TclpComputationForm>
 *
 * @method TclpComputationForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method TclpComputationForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method TclpComputationForm[]    findAll()
 * @method TclpComputationForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TclpComputationFormRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, TclpComputationForm::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(
        string $quarter,
        string $fieldOffice,
        string $data,
        int $createdBy,
    ): void {
        $entity = new TclpComputationForm();
        $entity->setQuarter($quarter);
        $entity->setFieldOffice($fieldOffice);
        $entity->setData($data);
        $entity->setCreatedBy($createdBy);
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(TclpComputationForm $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
