<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\RjRelatedRestitutions;
use App\Model\RjRelatedRestitutions as RjRelatedRestitutionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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
    ) {
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

        return $this->helper->createCachedResponse($params, function () {
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
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function create(RjRelatedRestitutionsModel $data): int | null
    {
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
        $newRjRelatedRestitution->setPaymentDate(
            $this->appDateHelper->convertStringToImmutableDate($data->getPaymentDate())
        );
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
     */
    public function delete(int $id): bool
    {
        $entity = $this->isExistingById($id);

        if (! $entity) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $entity =$this->isExistingById($id);

        if ($entity == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | RjRelatedRestitutions
    {
        $entity = $this->findOneBy([
            'rjRelatedRestitutionId' => $id,
            'deletedAt' => null
        ]);

        return ($entity == null) ? false : $entity;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getRJIB3Data(int $quarterId, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getRJIB3Key($quarterId, $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function () use ($quarterId, $fieldOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT rjrr.*, c.first_name, c.middle_name, c.last_name, c.gender, o.name as offense,
                    pf.name as payment_form, pm.name as payment_mode FROM rj_related_restitutions as rjrr " .
                "LEFT JOIN clients as c ON rjrr.client_id = c.client_id " .
                "LEFT JOIN offenses as o ON rjrr.offense_id = o.offenses_id " .
                "LEFT JOIN payment_forms as pf ON rjrr.payment_form_id = pf.payment_form_id " .
                "LEFT JOIN payment_modes as pm ON rjrr.payment_mode_id = pm.payment_mode_id " .
                "WHERE rjrr.quarter_id = $quarterId AND rjrr.field_office_id = $fieldOfficeId ".
                "AND rjrr.deleted_at IS NULL ORDER BY rjrr.rj_group";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    public function findByFieldOfficesId(int $quarterId, array $fieldOfficesId): array
    {
        $response = [];
        $results = $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT * FROM rj_related_restitutions rrr WHERE rrr.field_office_id IN (:fieldOfficesId)
                        AND rrr.quarter_id = :quarterId",
                [
                    'fieldOfficesId' => $fieldOfficesId,
                    'quarterId' => $quarterId,
                ],
                ['fieldOfficesId' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();

        foreach ($results as $result) {
            $fieldOfficeId = $result['field_office_id'];

            if (! isset($response[$fieldOfficeId])) {
                $response[$fieldOfficeId] = [];
            }

            $response[$fieldOfficeId][] = $result;
        }

        return $response;
    }
}
