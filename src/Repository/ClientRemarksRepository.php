<?php

namespace App\Repository;

use App\Entity\ClientRemarks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientRemarks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientRemarks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientRemarks[]    findAll()
 * @method ClientRemarks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRemarksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientRemarks::class);
    }
}
