<?php

namespace App\Repository;

use App\Entity\Orderproducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Orderproducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orderproducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orderproducts[]    findAll()
 * @method Orderproducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderproductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orderproducts::class);
    }

    // /**
    //  * @return Orderproducts[] Returns an array of Orderproducts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Orderproducts
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
