<?php

namespace App\Repository;

use App\Entity\SessionRemarks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SessionRemarks|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionRemarks|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionRemarks[]    findAll()
 * @method SessionRemarks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRemarksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionRemarks::class);
    }
}
