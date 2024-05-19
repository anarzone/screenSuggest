<?php

namespace App\Repository;

use App\Entity\UserContentRecommendation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserContentRecommendation>
 *
 * @method UserContentRecommendation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserContentRecommendation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserContentRecommendation[]    findAll()
 * @method UserContentRecommendation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserContentRecommendationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserContentRecommendation::class);
    }

//    /**
//     * @return UserContentRecommendation[] Returns an array of UserContentRecommendation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserContentRecommendation
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
