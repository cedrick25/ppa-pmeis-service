<?php

namespace App\Repository;

use App\Entity\RJConductProcesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RJConductProcesses|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJConductProcesses|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJConductProcesses[]    findAll()
 * @method RJConductProcesses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJConductProcessesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RJConductProcesses::class);
    }

}
