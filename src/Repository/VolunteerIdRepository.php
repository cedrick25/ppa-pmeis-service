<?php

namespace App\Repository;

use App\Entity\VolunteerId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VolunteerId|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerId|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerId[]    findAll()
 * @method VolunteerId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VolunteerId::class);
    }
}
