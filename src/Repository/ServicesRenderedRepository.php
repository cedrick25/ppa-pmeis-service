<?php

namespace App\Repository;

use App\Entity\ServicesRendered;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicesRendered|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicesRendered|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicesRendered[]    findAll()
 * @method ServicesRendered[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicesRenderedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicesRendered::class);
    }
}
