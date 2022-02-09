<?php

namespace App\Repository;

use App\Entity\Religion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Religion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Religion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Religion[]    findAll()
 * @method Religion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReligionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Religion::class);
    }
}
