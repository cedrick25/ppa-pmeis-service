<?php

namespace App\Repository;

use App\Entity\Venues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Venues|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venues|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venues[]    findAll()
 * @method Venues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenuesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venues::class);
    }

    public function create(string $name): int|null
    {
        return 1;
    }
}
