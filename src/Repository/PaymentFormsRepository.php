<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\PaymentForms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

/**
 * @method PaymentForms|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentForms|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentForms[]    findAll()
 * @method PaymentForms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentFormsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "payment_forms";

    public function __construct(
        ManagerRegistry $registry,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, PaymentForms::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return PaymentForms[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllPaymentFormsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('pf')
                ->where('pf.deletedAt IS NULL')
                ->orderBy('pf.paymentFormId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | PaymentForms
    {
        $paymentForm = $this->findOneBy([
            'paymentFormId' => $id,
            'deletedAt' => null
        ]);

        return ($paymentForm == null) ? false : $paymentForm;
    }
}
