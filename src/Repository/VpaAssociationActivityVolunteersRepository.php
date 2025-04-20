<?php

namespace App\Repository;

use App\Entity\VpaAssociationActivityVolunteers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpaAssociationActivityVolunteers>
 *
 * @method VpaAssociationActivityVolunteers|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpaAssociationActivityVolunteers|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpaAssociationActivityVolunteers[]    findAll()
 * @method VpaAssociationActivityVolunteers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpaAssociationActivityVolunteersRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, VpaAssociationActivityVolunteers::class);
  }

  public function batchCreate(int $vpaAssociationActivityId, array $volunteers): void
  {
      foreach ($volunteers as $volunteer) {
          $entity = new VpaAssociationActivityVolunteers();
          $entity->setVpaActivityVounteerId($vpaAssociationActivityId);
          $entity->setVolunteerId($volunteer['id']['value']);
          $entity->setRole($volunteer['role']);

          $this->_em->persist($entity);
      }

      $this->_em->flush();
      $this->_em->clear(VpaAssociationActivityVolunteers::class);
  }
  
  public function fetchForReportByVpaAssociationId(int $id): array
  {
    $query = $this->getEntityManager()->getConnection()->executeQuery(
        "SELECT v.volunteer_id, v.first_name, v.middle_name, v.last_name, v.gender, vaav.role
          FROM vpa_association_activity_volunteers as vaav
          LEFT JOIN volunteer as v ON v.volunteer_id = vaav.volunteer_id
          WHERE vaav.vpa_activity_vounteer_id = :id",
        ['id' => $id]
    );

    return $query->fetchAllAssociative();
  }

  public function fetchByVpaAssociationId(int $id): array
  {
    $query = $this->getEntityManager()->getConnection()->executeQuery(
        "SELECT v.volunteer_id, v.first_name, v.middle_name, v.last_name, vaav.role
          FROM vpa_association_activity_volunteers as vaav
          LEFT JOIN volunteer as v ON v.volunteer_id = vaav.volunteer_id
          WHERE vaav.vpa_activity_vounteer_id = :id",
        ['id' => $id]
    );

    $results = $query->fetchAllAssociative();

    if (\count($results) === 0) {
        return [];
    }

    $return = [];

    foreach ($results as $result) {
      $return[] = [
        "id" => [
          "label" => "{$result["last_name"]}, {$result["first_name"]} {$result["middle_name"]}",
          "value" => $result["volunteer_id"]
        ],
        "role" => $result["role"]
      ];
    }

    return $return;
  }

  public function deleteByVpaAssociationId(int $id): void
  {
    $this->getEntityManager()->getConnection()
      ->executeQuery(
          "DELETE FROM vpa_association_activity_volunteers WHERE vpa_association_activity_volunteers.vpa_activity_vounteer_id = :id",
          ['id' => $id]
      );
  }
}
