<?php


namespace App\Repository;


use App\Entity\Meter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MeterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Meter::class);
    }

    /**
     * Returns all meters ordered by creation date (ASC) with pagination
     *
     * @param int $limit
     * @param int $offset
     *
     * @return Meter[]
     */
    public function findAllPaginated(int $limit, int $offset)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('m.created', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
