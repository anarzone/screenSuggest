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
            ->setMaxResults($limit)
            ->andWhere("COALESCE(TRIM(m.title), '') <> ''")
            ->groupBy('m.id');

        // Add a left join with reviews to calculate average in-site rating
//        $qb->leftJoin('m.reviews', 'r')
//           ->groupBy('m.id');

        if ($filter->genre !== null) {
            $qb->join('m.genres', 'g')
               ->andWhere('g.name = :genre')
               ->setParameter('genre', $filter->genre);
        }

        if ($filter->year !== null) {
            $qb->andWhere('year(m.releaseDate) = :year')
               ->setParameter('year', $filter->year);
        }

        if ($filter->imdbRatingMin !== null) {
            $qb->andHaving('AVG(m.imdbRating) >= :min')
               ->setParameter('min', $filter->imdbRatingMin);
        }

        if ($filter->imdbRatingMax !== null) {
            $qb->andHaving('AVG(m.imdbRating) <= :max')
               ->setParameter('max', $filter->imdbRatingMax);
        }

        $movies = $qb->getQuery()->getResult();

        // count total separately (or use Doctrine Paginator)
        $total = (clone $qb)
            ->select('COUNT(DISTINCT m.id)')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getResult();

        return [$movies, (int)$total];
    }
}
