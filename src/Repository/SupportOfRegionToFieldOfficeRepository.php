<?php

namespace App\Repository;

use App\Entity\SupportOfRegionToFieldOffice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupportOfRegionToFieldOffice|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportOfRegionToFieldOffice|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportOfRegionToFieldOffice[]    findAll()
 * @method SupportOfRegionToFieldOffice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportOfRegionToFieldOfficeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportOfRegionToFieldOffice::class);
    }
}
