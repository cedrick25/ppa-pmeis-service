<?php

namespace App\Repository;

use App\Entity\CivilStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CivilStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CivilStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CivilStatus[]    findAll()
 * @method CivilStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CivilStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CivilStatus::class);
    }
}
