<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Quarters;
use App\Enum\Response as ResponseEnum;
use App\Model\Quarters as QuartersModel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Quarters|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quarters|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quarters[]    findAll()
 * @method Quarters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuartersRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, Quarters::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws ORMException|\Psr\Cache\InvalidArgumentException
     */
    public function create(QuartersModel $quarters): int|null
    {
        $quarterByNameAndYear = $this->getByNameAndYear($quarters->getName(), $quarters->getYear());

        if ($quarterByNameAndYear != null) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllQuartersKey());

        $quarter = new Quarters();
        $quarter->setName($quarters->getName());
        $quarter->setYear($quarters->getYear());
        $quarter->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($quarter);
        $this->getEntityManager()->flush();

        return $quarter->getQuarterId();
    }

    /**
     * @param string $name
     * @param string $year
     * @return Quarters|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getByNameAndYear(string $name, string $year): ?Quarters
    {
        $cacheKey = $this->cacheHelper->getQuarterByNameAndYearKey($name, $year);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $name, $year) {
            $dateTimeExpiration = new \DateTime();
            $result = $this->createQueryBuilder('qtr')
                ->andWhere('qtr.name = :name AND qtr.year = :year')
                ->setParameter('name', $name)
                ->setParameter('year', $year)
                ->getQuery()
                ->getOneOrNullResult();

            // Immediately expires the cache if there is no record found.
            // This resolves the issue of checking the record if already exist before creating new one.
            if ($result == null) {
                $dateTimeExpiration->add(new \DateInterval("PT1S"));
            } else {
                $dateTimeExpiration->add(new \DateInterval("PT24H"));
            }

            $item->expiresAt($dateTimeExpiration);

            return $result;
        });
    }

    /**
     * @return Quarters[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllQuartersKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('qtr')
                ->orderBy('qtr.quarterId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @param int $id
     * @return Quarters|null
     * @throws NonUniqueResultException
     */
    public function getById(int $id): ?Quarters
    {
        return $this->createQueryBuilder('qtr')
            ->andWhere('qtr.quarterId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws ORMException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        // TODO: Check if there is an existing id in sessions
        $quarter = $this->getById($id);

        if ($quarter == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllQuartersKey());
        $this->cache->delete($this->cacheHelper->getQuarterByNameAndYearKey($quarter->getName(), $quarter->getYear()));

        $this->getEntityManager()->remove($quarter);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, QuartersModel $quarterData): string
    {
        $quarter = $this->isExistingById($id);

        if (! $quarter) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($quarter, $quarterData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->delete($this->cacheHelper->getAllQuartersKey());

        $quarter->setName($quarterData->getName());
        $quarter->setYear($quarterData->getYear());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Quarters
    {
        $quarter = $this->find($id);

        return ($quarter == null) ? false : $quarter;
    }

    public function isConflicted(Quarters $fetchedQuarter, QuartersModel $quarterData): bool
    {
        if (
            $fetchedQuarter->getName() === $quarterData->getName() &&
            $fetchedQuarter->getYear() === $quarterData->getYear()
        ) {
            return false;
        }

        $quarter = $this->findOneBy([
            'name' => $quarterData->getName(),
            'year' => $quarterData->getYear()
        ]);

        if ($quarter != null) {
            return true;
        }

        return false;
    }
}
