<?php

namespace App\Repository;

use App\Entity\VpaAssociationActivityVolunteersServices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpaAssociationActivityVolunteersServices>
 *
 * @method VpaAssociationActivityVolunteersServices|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpaAssociationActivityVolunteersServices|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpaAssociationActivityVolunteersServices[]    findAll()
 * @method VpaAssociationActivityVolunteersServices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpaAssociationActivityVolunteersServicesRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, VpaAssociationActivityVolunteersServices::class);
  }

  public function batchCreate(int $vpaActivityVolunteerId, array $volunteers): void
  {
      foreach ($volunteers as $volunteer) {
          $entity = new VpaAssociationActivityVolunteersServices;
          $entity->setVpaActivityVolunteerId($vpaActivityVolunteerId);
          $entity->setServiceRenderedId($volunteer['value']);

          $this->_em->persist($entity);
      }

      $this->_em->flush();
      $this->_em->clear(VpaAssociationActivityVolunteersServices::class);
  }

  public function fetchByVpaAssociationId(int $id): array
  {
    $query = $this->getEntityManager()->getConnection()->executeQuery(
        "SELECT vpa_activity_volunteer_id, service_rendered_id, name from vpa_association_activity_volunteers_services as vaavs
        left join services_rendered as sr ON sr.services_rendered_id = vaavs.service_rendered_id
        where vaavs.vpa_activity_volunteer_id = :id",
        ['id' => $id]
    );

    $results = $query->fetchAllAssociative();

    if (\count($results) === 0) {
        return [];
    }

    $return = [];

    foreach ($results as $result) {
      $return[] = [
         "label" => $result["name"],
         "value" => $result["service_rendered_id"]
      ];
    }

    return $return;
  }
  
  
}
