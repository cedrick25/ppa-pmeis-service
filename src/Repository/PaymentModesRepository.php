<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\PaymentModes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

/**
 * @method PaymentModes|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentModes|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentModes[]    findAll()
 * @method PaymentModes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentModesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "payment_modes";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, PaymentModes::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return PaymentModes[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllPaymentModesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('pm')
                ->where('pm.deletedAt IS NULL')
                ->orderBy('pm.paymentModeId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | PaymentModes
    {
        $paymentMode = $this->findOneBy([
            'paymentModeId' => $id,
            'deletedAt' => null
        ]);

        return ($paymentMode == null) ? false : $paymentMode;
    }
}
