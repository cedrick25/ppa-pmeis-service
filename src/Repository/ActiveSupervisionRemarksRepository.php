<?php

namespace App\Repository;

use App\Entity\ActiveSupervisionRemarks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActiveSupervisionRemarks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActiveSupervisionRemarks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActiveSupervisionRemarks[]    findAll()
 * @method ActiveSupervisionRemarks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveSupervisionRemarksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveSupervisionRemarks::class);
    }
}
