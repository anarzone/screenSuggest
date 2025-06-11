<?php

namespace App\Repository;

use App\Domain\Content\Dto\Movie\MovieFilter;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * Find paginated movies
     *
     * @param MovieFilter $filter
     * @param int $limit Maximum number of movies to return
     * @param int $offset Starting position
     * @return array
     */
    public function getPaginatedFiltered(
        MovieFilter $filter,
        int $limit,
        int $offset
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($filter->genre !== null) {
            $qb->andWhere('m.genre = :genre')
                ->setParameter('genre', $filter->genre);
        }

        if ($filter->year !== null) {
            $qb->andWhere('m.releaseDate = :year')
                ->setParameter('year', $filter->year);
        }

        if ($filter->ratingMin !== null) {
            $qb->andWhere('m.averageRating >= :min')
                ->setParameter('min', $filter->ratingMax);
        }

        // …add other filter criteria similarly…

        $movies = $qb->getQuery()->getResult();

        // count total separately (or use Doctrine Paginator)
        $total = (clone $qb)
            ->select('COUNT(m.id)')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getSingleScalarResult();

        return [$movies, (int)$total];
    }

    /**
     * Count total number of movies
     *
     * @return int
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Movie[] Returns an array of Movie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Movie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
