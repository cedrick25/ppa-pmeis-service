<?php

namespace App\Repository;

use App\Entity\EducationBackground;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EducationBackground|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationBackground|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationBackground[]    findAll()
 * @method EducationBackground[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationBackgroundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationBackground::class);
    }
}
