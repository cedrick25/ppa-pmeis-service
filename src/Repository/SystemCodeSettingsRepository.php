<?php

namespace App\Repository;

use App\Entity\SystemCodeSettings;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<SystemCodeSettings>
 *
 * @method SystemCodeSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemCodeSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemCodeSettings[]    findAll()
 * @method SystemCodeSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemCodeSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemCodeSettings::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(
        string $name,
        string $value,
        int $createdBy
    ): string {
        if (! is_bool($this->getByName($name))) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $entity = new SystemCodeSettings();
        $entity->setSystemCodeId(Uuid::v4()->toRfc4122());
        $entity->setName($name);
        $entity->setValue($value);
        $entity->setCreatedBy($createdBy);
        $this->_em->persist($entity);
        $this->_em->flush();

        return ResponseEnum::OK;
    }

    public function update(
        string $id,
        string $name,
        string $value,
        int $updatedBy
    ): string {
        $entity = $this->findOneBy([
            'systemCodeId' => $id
        ]);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setName($name);
        $entity->setValue($value);
        $entity->setCreatedBy($updatedBy);

        $this->_em->flush();

        return ResponseEnum::OK;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(string $id): string
    {
        $entity = $this->findOneBy([
            'systemCodeId' =>  $id
        ]);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $this->_em->remove($entity);
        $this->_em->flush();

        return ResponseEnum::OK;
    }

    public function getByName(string $name): bool | SystemCodeSettings
    {
        $entity = $this->findOneBy([
            'name' => $name
        ]);

        return $entity != null ? $entity : false;
    }
}
