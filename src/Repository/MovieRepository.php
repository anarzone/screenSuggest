<?php

namespace App\Repository;

use App\Domain\Content\Dto\Movie\MovieFilter;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function searchAndFilter(MovieFilter $filter, int $limit, int $offset): array
    {
        if ($filter->hasSearch()){
            return $this->searchWithFilters($filter, $limit, $offset);
        }

        return $this->getPaginatedFiltered($filter, $limit, $offset);
    }

    public function searchWithFilters(MovieFilter $filter, int $limit, int $offset): array
    {
        $query = $filter->query;
        $category = $filter->category ?? 'all';
        $searchLimit = 200; // update this limit based on your needs

        $searchResults = $this->performAdvancedSearch($query, $category, $searchLimit);

        if (empty($searchResults)) {
            return [[], 0];
        }

        if (!$filter->hasFilters()){
            $total = count($searchResults);
            $paginatedResults = array_slice($searchResults, $offset, $limit);
            return [$paginatedResults, $total];
        }

        // Apply filters to search results
        $movieIds = array_map(fn($movie) => $movie->getId(), $searchResults);

        $qb = $this->createQueryBuilder('m');
        $countQb = $this->createQueryBuilder('m')->select('COUNT(DISTINCT m.id)');

        // Filter by search results
        $qb->where('m.id IN (:ids)')->setParameter('ids', $movieIds);
        $countQb->where('m.id IN (:ids)')->setParameter('ids', $movieIds);

        // Apply additional filters
        $this->applyFilters($qb, $filter);
        $this->applyFilters($countQb, $filter);

        $total = $countQb->getQuery()->getSingleScalarResult();

        // Preserve search relevance order by using a custom ORDER BY
        $qb->addSelect('FIELD(m.id, :order_ids) as HIDDEN field_order')
            ->setParameter('order_ids', $movieIds)
            ->orderBy('field_order', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $movies = $qb->getQuery()->getResult();

        return [$movies, $total];

    }

    private function applyFilters($qb, MovieFilter $filter): void
    {
        if ($filter->genre) {
            $qb->leftJoin('m.genres', 'filter_genre')
            ->andWhere('filter_genre.name LIKE :genre')
            ->setParameter('genre', '%' . $filter->genre . '%');
        }

        if ($filter->year) {
            $qb->andWhere('YEAR(m.releaseDate) = :year')
                ->setParameter('year', $filter->year);
        }

        if ($filter->imdbRatingMin) {
            $qb->andWhere('m.imdbRating >= :minRating')
                ->setParameter('minRating', $filter->imdbRatingMin);
        }

        if ($filter->imdbRatingMax) {
            $qb->andWhere('m.imdbRating <= :maxRating')
                ->setParameter('maxRating', $filter->imdbRatingMax);
        }
    }

    private function performAdvancedSearch(string $query, string $category, int $limit): array
    {
        if (strlen(trim($query)) < 3) {
            return [];
        }

        // Category-specific search
        if ($category !== 'all') {
            return match ($category) {
                'genre' => $this->searchByGenre($query, $limit),
                'actor' => $this->searchByActor($query, $limit),
                'director' => $this->searchByDirector($query, $limit),
                default => []
            };
        }

        // Full search with fallback strategies (same logic as MovieSearchService)

        // Step 1: Try exact title matches first
        $exactMatches = $this->searchMoviesByCriteria($query, $limit, 50); // Todo: adjust threshold as needed

        if (!empty($exactMatches)) {
            return array_slice($exactMatches, 0, $limit);
        }

        // Step 2: Try full-text search with medium threshold
        $fullTextMatches = $this->searchMoviesByCriteria($query, $limit, 2); // Todo: adjust threshold as needed
        if (count($fullTextMatches) >= $limit) {
            return array_slice($fullTextMatches, 0, $limit);
        }

        // Step 3: Try searching with related entities
        $additionalMovies = $this->searchMoviesWithRelations($query, $limit);
        $movies = $this->mergeResults($fullTextMatches, $additionalMovies, $limit);

        // Step 4: Final fallback with very low threshold
        if (count($movies) < $limit / 2) {
            $fallbackMatches = $this->searchMoviesByCriteria($query, $limit * 2, 1);
            $movies = $this->mergeResults($movies, $fallbackMatches, $limit);
        }

        return array_slice($movies, 0, $limit);
    }

    private function mergeResults(array $primary, array $secondary, int $limit): array
    {
        $seen = [];
        $merged = [];

        // Add primary results first (they have higher relevance)
        foreach ($primary as $movie) {
            if (!isset($seen[$movie->getId()]) && count($merged) < $limit) {
                $merged[] = $movie;
                $seen[$movie->getId()] = true;
            }
        }

        // Add secondary results if we need more
        foreach ($secondary as $movie) {
            if (!isset($seen[$movie->getId()]) && count($merged) < $limit) {
                $merged[] = $movie;
                $seen[$movie->getId()] = true;
            }
        }

        return $merged;
    }

    public function searchMoviesByCriteria(string $query, int $limit = 10, float $minScore = 0.1): array
    {
        $sql = '
            SELECT m.*,
                   (
                       -- Exact title match gets highest score
                       CASE 
                           WHEN LOWER(m.title) = LOWER(:exact_query) THEN 100
                           WHEN LOWER(m.original_title) = LOWER(:exact_query) THEN 95
                           ELSE 0
                       END +
                       -- Title starts with query gets high score
                       CASE 
                           WHEN LOWER(m.title) LIKE LOWER(CONCAT(:like_query, "%")) THEN 50
                           WHEN LOWER(m.original_title) LIKE LOWER(CONCAT(:like_query, "%")) THEN 45
                           ELSE 0
                       END +
                       -- Full-text search scores - using the composite index
                       MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE) * 10 +
                       -- Production companies/countries full-text search
                       MATCH(m.production_companies, m.production_countries) AGAINST(:query IN BOOLEAN MODE) * 2 +
                       -- Phrase matching bonus
                       CASE 
                           WHEN m.title LIKE CONCAT("%", :like_query, "%") THEN 20
                           WHEN m.original_title LIKE CONCAT("%", :like_query, "%") THEN 15
                           ELSE 0
                       END
                   ) as relevance_score
            FROM movies m
            WHERE (
                LOWER(m.title) = LOWER(:exact_query) OR
                LOWER(m.original_title) = LOWER(:exact_query) OR
                LOWER(m.title) LIKE LOWER(CONCAT(:like_query, "%")) OR
                LOWER(m.original_title) LIKE LOWER(CONCAT(:like_query, "%")) OR
                MATCH(m.title , m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE) OR
                MATCH(m.production_companies, m.production_countries) AGAINST(:query IN BOOLEAN MODE) OR
                m.title LIKE CONCAT("%", :like_query, "%") OR
                m.original_title LIKE CONCAT("%", :like_query, "%") OR
                -- Check for genre matches
                EXISTS (
                    SELECT 1 FROM movie_genre mg 
                    JOIN genres g ON mg.genre_id = g.id 
                    WHERE mg.movie_id = m.id AND MATCH(g.name) AGAINST(:query IN BOOLEAN MODE)
                ) OR
                -- Check for actor matches  
                EXISTS (
                    SELECT 1 FROM movie_actor ma 
                    JOIN actors a ON ma.actor_id = a.id 
                    WHERE ma.movie_id = m.id AND MATCH(a.name, a.biography, a.also_known_as) AGAINST(:query IN BOOLEAN MODE)
                ) OR
                -- Check for director matches
                EXISTS (
                    SELECT 1 FROM movie_director md 
                    JOIN directors d ON md.director_id = d.id 
                    WHERE md.movie_id = m.id AND MATCH(d.name) AGAINST(:query IN BOOLEAN MODE)
                )
            )
            HAVING relevance_score > :min_score
            ORDER 
                BY (LOWER(m.title) = LOWER(:exact_query))    DESC,
                   (LOWER(m.original_title) = LOWER(:exact_query)) DESC,
                   relevance_score DESC,
                   m.imdb_rating   DESC
            LIMIT :limit
        ';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('query', $this->prepareSearchQuery($query));
        $stmt->bindValue('exact_query', trim($query));
        $stmt->bindValue('like_query', trim($query));
        $stmt->bindValue('min_score', $minScore);
        $stmt->bindValue('limit', $limit, ParameterType::INTEGER);

        $result = $stmt->executeQuery();
        $movieData = $result->fetchAllAssociative();

        $movies = [];
        foreach ($movieData as $row) {
            $movie = $this->find($row['id']);
            if ($movie) {
                $movies[] = $movie;
            }
        }

        return $movies;
    }

    public function searchMoviesWithRelations(string $query, int $limit = 10): array
    {
        $sql = '
            SELECT DISTINCT m.id, 
                   (
                       -- Movie title/description matching (highest priority) - using composite indexes
                       COALESCE(MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE), 0) * 8 +
                       COALESCE(MATCH(m.production_companies, m.production_countries) AGAINST(:query IN BOOLEAN MODE), 0) * 3 +
                       -- Related entities (lower priority) - using their respective indexes
                       COALESCE(MATCH(g.name) AGAINST(:query IN BOOLEAN MODE), 0) * 2 +
                       COALESCE(MATCH(a.name, a.biography, a.also_known_as) AGAINST(:query IN BOOLEAN MODE), 0) * 2 +
                       COALESCE(MATCH(d.name) AGAINST(:query IN BOOLEAN MODE), 0) * 2 +
                       -- Exact matches bonus
                       CASE WHEN LOWER(m.title) = LOWER(:exact_query) THEN 50 ELSE 0 END +
                       CASE WHEN LOWER(m.original_title) = LOWER(:exact_query) THEN 45 ELSE 0 END
                   ) as total_score
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            LEFT JOIN movie_actor ma ON m.id = ma.movie_id
            LEFT JOIN actors a ON ma.actor_id = a.id
            LEFT JOIN movie_director md ON m.id = md.director_id
            LEFT JOIN directors d ON md.director_id = d.id
            WHERE (
                MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE) OR
                MATCH(m.production_companies, m.production_countries) AGAINST(:query IN BOOLEAN MODE) OR
                MATCH(g.name) AGAINST(:query IN BOOLEAN MODE) OR
                MATCH(a.name, a.biography, a.also_known_as) AGAINST(:query IN BOOLEAN MODE) OR
                MATCH(d.name) AGAINST(:query IN BOOLEAN MODE) OR
                LOWER(m.title) = LOWER(:exact_query) OR
                LOWER(m.original_title) = LOWER(:exact_query)
            )
            HAVING total_score > 0.1
            ORDER BY total_score DESC, m.imdb_rating DESC
            LIMIT :limit
        ';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('query', $this->prepareSearchQuery($query));
        $stmt->bindValue('exact_query', trim($query));
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $result = $stmt->executeQuery();
        $movieIds = array_column($result->fetchAllAssociative(), 'id');

        if (empty($movieIds)) {
            return [];
        }

        // Preserve the order from our relevance scoring
        $movies = [];
        foreach ($movieIds as $id) {
            $movie = $this->find($id);
            if ($movie) {
                $movies[] = $movie;
            }
        }

        return $movies;
    }

    public function searchByGenre(string $genreName, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.genres', 'g')
            ->where('MATCH(g.name) AGAINST(:query IN BOOLEAN MODE)')
            ->setParameter('query', $this->prepareSearchQuery($genreName))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByActor(string $actorName, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.actors', 'a')
            ->where('MATCH(a.name, a.biography, a.also_known_as) AGAINST(:query IN BOOLEAN MODE)')
            ->setParameter('query', $this->prepareSearchQuery($actorName))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByDirector(string $directorName, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.directors', 'd')
            ->where('MATCH(d.name) AGAINST(:query IN BOOLEAN MODE)')
            ->setParameter('query', $this->prepareSearchQuery($directorName))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getSearchSuggestions(string $query, int $limit = 5): array
    {
        if (strlen($query) < 3) {
            return [];
        }

        $sql = '
            SELECT DISTINCT m.title, 
                   (
                       CASE WHEN LOWER(m.title) LIKE LOWER(CONCAT(:like_query, "%")) THEN 10 ELSE 0 END +
                       MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE)
                   ) as score
            FROM movies m
            WHERE (
                LOWER(m.title) LIKE LOWER(CONCAT(:like_query, "%")) OR
                MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE)
            )
            ORDER BY score DESC
            LIMIT :limit
        ';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('query', $this->prepareSearchQuery($query));
        $stmt->bindValue('like_query', trim($query));
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $result = $stmt->executeQuery();
        return array_column($result->fetchAllAssociative(), 'title');
    }

    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('m');

        if (!empty($criteria['query'])) {
            $sql = '
                SELECT m.id
                FROM movies m
                WHERE MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE)
                ORDER BY MATCH(m.title, m.original_title, m.description) AGAINST(:query IN BOOLEAN MODE) DESC
            ';

            $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
            $stmt->bindValue('query', $this->prepareSearchQuery($criteria['query']));
            $result = $stmt->executeQuery();
            $movieIds = array_column($result->fetchAllAssociative(), 'id');

            if (!empty($movieIds)) {
                $qb->where('m.id IN (:ids)')
                   ->setParameter('ids', $movieIds);
            } else {
                return [];
            }
        }

        if (!empty($criteria['genre'])) {
            $qb->join('m.genres', 'g')
               ->andWhere('g.name LIKE :genre')
               ->setParameter('genre', '%' . $criteria['genre'] . '%');
        }

        if (!empty($criteria['year'])) {
            $qb->andWhere('YEAR(m.releaseDate) = :year')
               ->setParameter('year', $criteria['year']);
        }

        if (!empty($criteria['minRating'])) {
            $qb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) >= :minRating')
               ->setParameter('minRating', $criteria['minRating']);
        }

        if (!empty($criteria['maxRating'])) {
            $qb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) <= :maxRating')
               ->setParameter('maxRating', $criteria['maxRating']);
        }

        return $qb->getQuery()->getResult();
    }

    public function getPaginatedFiltered(MovieFilter $filter, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('m');
        $countQb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)');

        if ($filter->genre) {
            $qb->join('m.genres', 'g')
               ->andWhere('g.name LIKE :genre')
               ->setParameter('genre', '%' . $filter->genre . '%');

            $countQb->join('m.genres', 'g')
                    ->andWhere('g.name LIKE :genre')
                    ->setParameter('genre', '%' . $filter->genre . '%');
        }

        if ($filter->year) {
            $qb->andWhere('YEAR(m.releaseDate) = :year')
               ->setParameter('year', $filter->year);

            $countQb->andWhere('YEAR(m.releaseDate) = :year')
                    ->setParameter('year', $filter->year);
        }

        if ($filter->imdbRatingMin) {
            $qb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) >= :minRating')
               ->setParameter('minRating', $filter->imdbRatingMin);

            $countQb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) >= :minRating')
                    ->setParameter('minRating', $filter->imdbRatingMin);
        }

        if ($filter->imdbRatingMax) {
            $qb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) <= :maxRating')
               ->setParameter('maxRating', $filter->imdbRatingMax);

            $countQb->andWhere('CAST(m.imdbRating AS DECIMAL(3,1)) <= :maxRating')
                    ->setParameter('maxRating', $filter->imdbRatingMax);
        }

        $total = $countQb->getQuery()->getSingleScalarResult();

        $movies = $qb->setFirstResult($offset)
                     ->setMaxResults($limit)
                     ->orderBy('m.id', 'DESC')
                     ->getQuery()
                     ->getResult();

        return [$movies, $total];
    }

    /**
     * Prepare search query for boolean mode
     */
    private function prepareSearchQuery(string $query): string
    {
        $query = trim($query);

        // Handle quoted phrases
        if (preg_match('/^"(.+)"$/', $query, $matches)) {
            return '"' . $matches[1] . '"';
        }

        // Split into words and prepare boolean search
        $words = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) === 1) {
            // Single word - search with wildcard
            return '+' . $words[0] . '*';
        }

        // Multiple words - require all words with wildcards
        return '+' . implode('* +', $words) . '*';
    }
}
