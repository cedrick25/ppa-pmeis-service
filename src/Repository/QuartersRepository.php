<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\Quarters;
use App\Model\Quarters as QuartersModel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
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

        $currentDateTime = new DateTimeImmutable();
        $currentDateTime->format("Y-m-d H:m:s");

        $quarter = new Quarters();
        $quarter->setName($quarters->getName());
        $quarter->setYear($quarters->getYear());
        $quarter->setCreatedAt($currentDateTime);

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
        $quarterById = $this->getById($id);

        if ($quarterById == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllQuartersKey());
        $this->cache->delete($this->cacheHelper->getQuarterByNameAndYearKey($quarterById->getName(), $quarterById->getYear()));

        $this->getEntityManager()->remove($quarterById);
        $this->getEntityManager()->flush();

        return true;
    }
}
