<?php

namespace App\Repository;

use App\Entity\TvShowGenre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TvShowGenre>
 *
 * @method TvShowGenre|null find($id, $lockMode = null, $lockVersion = null)
 * @method TvShowGenre|null findOneBy(array $criteria, array $orderBy = null)
 * @method TvShowGenre[]    findAll()
 * @method TvShowGenre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TvShowGenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TvShowGenre::class);
    }

    //    /**
    //     * @return TvShowGenre[] Returns an array of TvShowGenre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TvShowGenre
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
