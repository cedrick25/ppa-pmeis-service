<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\RjRelatedRestitutions;
use App\Model\RjRelatedRestitutions as RjRelatedRestitutionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method RjRelatedRestitutions|null find($id, $lockMode = null, $lockVersion = null)
 * @method RjRelatedRestitutions|null findOneBy(array $criteria, array $orderBy = null)
 * @method RjRelatedRestitutions[]    findAll()
 * @method RjRelatedRestitutions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RjRelatedRestitutionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "rj_related_restitutions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, RjRelatedRestitutions::class);
    }

    /**
     * @return RjRelatedRestitutions[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRJRelatedRestitutionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('rjrr')
                ->where('rjrr.deletedAt IS NULL')
                ->orderBy('rjrr.rjRelatedRestitutionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @param RjRelatedRestitutionsModel $data
     * @return int|null
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function create(RjRelatedRestitutionsModel $data): int | null
    {
        if ($this->isExisting($data)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newRjRelatedRestitution = new RjRelatedRestitutions();
        $newRjRelatedRestitution->setQuarterId($data->getQuarterId());
        $newRjRelatedRestitution->setFieldOfficeId($data->getFieldOfficeId());
        $newRjRelatedRestitution->setClientId($data->getClientId());
        $newRjRelatedRestitution->setRjGroup($data->getRjGroup());
        $newRjRelatedRestitution->setOffenseId($data->getOffenseId());
        $newRjRelatedRestitution->setOriginalAmount($data->getOriginalAmount());
        $newRjRelatedRestitution->setStartOfQuarter($data->getStartOfQuarter());
        $newRjRelatedRestitution->setAmountPaid($data->getAmountPaid());
        $newRjRelatedRestitution->setBalance($data->getBalance());
        $newRjRelatedRestitution->setPaymentFormId($data->getPaymentFormId());
        $newRjRelatedRestitution->setPaymentModeId($data->getPaymentModeId());
        $newRjRelatedRestitution->setPaymentDate($this->appDateHelper->convertStringToImmutableDate($data->getPaymentDate()));
        $newRjRelatedRestitution->setPaymentAmount($data->getPaymentAmount());
        $newRjRelatedRestitution->setPaymentRecipient($data->getPaymentRecipient());
        $newRjRelatedRestitution->setRemittedTo($data->getRemittedTo());
        $newRjRelatedRestitution->setRemittedAmount($data->getRemittedAmount());
        $newRjRelatedRestitution->setRemarks($data->getRemarks());
        $newRjRelatedRestitution->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newRjRelatedRestitution);
        $this->getEntityManager()->flush();

        return $newRjRelatedRestitution->getRjRelatedRestitutionId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $RJRelatedRestitutions = $this->isExistingById($id);
        if (! $RJRelatedRestitutions) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($RJRelatedRestitutions);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $RJRelatedRestitution =$this->isExistingById($id);

        if ($RJRelatedRestitution == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $RJRelatedRestitution->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | RjRelatedRestitutions
    {
        $RJRelatedRestitution = $this->findOneBy([
            'rjRelatedRestitutionId' => $id,
            'deletedAt' => null
        ]);

        return ($RJRelatedRestitution == null) ? false : $RJRelatedRestitution;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getRJIB3Data(int $clientId, int $quarterId, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getRJIB3Key($clientId, $quarterId, $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use($clientId, $quarterId, $fieldOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rjrr.*, c.first_name, c.middle_name, c.last_name, c.gender, o.name as offense, pf.name as payment_form, pm.name as payment_mode
                    FROM rj_related_restitutions as rjrr " .
                "LEFT JOIN clients as c ON rjrr.client_id = c.client_id " .
                "LEFT JOIN offenses as o ON rjrr.offense_id = o.offenses_id " .
                "LEFT JOIN payment_forms as pf ON rjrr.payment_form_id = pf.payment_form_id " .
                "LEFT JOIN payment_modes as pm ON rjrr.payment_mode_id = pm.payment_mode_id " .
                "WHERE rjrr.client_id = $clientId AND rjrr.quarter_id = $quarterId AND rjrr.field_office_id = $fieldOfficeId ".
                "AND rjrr.deleted_at IS NULL ORDER BY rjrr.rj_group";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    private function isExisting(RjRelatedRestitutionsModel $data): bool | RjRelatedRestitutions
    {
        $RJRelatedRestitution = $this->findOneBy([
            'clientId' => $data->getClientId(),
            'quarterId' => $data->getQuarterId(),
            'fieldOfficeId' => $data->getFieldOfficeId(),
            'deletedAt' => null
        ]);

        return ($RJRelatedRestitution == null) ? false : $RJRelatedRestitution;
    }
}
