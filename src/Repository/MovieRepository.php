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
        if ($filter->hasSearch()) {
            return $this->searchWithFilters($filter, $limit, $offset);
        }

        return $this->getPaginatedFiltered($filter, $limit, $offset);
    }

    public function searchWithFilters(MovieFilter $filter, int $limit, int $offset): array
    {
        $searchResults = $this->performAdvancedSearch($filter);

        if (empty($searchResults)) {
            return [[], 0];
        }

        if (!$filter->hasFilters()) {
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

        if ($filter->yearStart && $filter->yearEnd) {
            $qb->andWhere('m.releaseDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'))
                ->setParameter('endDate',   new \DateTimeImmutable($filter->yearEnd   . '-12-31'));
        } else {
            if ($filter->yearStart) {
                $qb->andWhere('m.releaseDate >= :startDate')
                    ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'));
            }
            if ($filter->yearEnd) {
                $qb->andWhere('m.releaseDate <= :endDate')
                    ->setParameter('endDate', new \DateTimeImmutable($filter->yearEnd . '-12-31'));
            }
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

    private function performAdvancedSearch(MovieFilter $filter): array
    {
        $query = $filter->query;
        $limit = 200;

        if (strlen(trim($query)) < 3) {
            return [];
        }

        // Use fuzzy search for better results
        return $this->searchMoviesByCriteriaWithFuzzy($filter, $limit);
    }

    public function searchMoviesByCriteria(MovieFilter $filter, int $limit = 10, float $minScore = 0.1): array
    {
        $query = $filter->query;
        $sql = '
            WITH ft AS (
                SELECT
                    id,
                    MATCH(title, original_title, description) AGAINST(:query IN BOOLEAN MODE) * 10 +
                    MATCH(production_companies, production_countries) AGAINST(:query IN BOOLEAN MODE) * 2
                  AS text_score
                FROM movies
                WHERE
                    imdb_rating IS NOT NULL AND imdb_rating > 0 AND
                    (MATCH(title, original_title, description) AGAINST(:query IN BOOLEAN MODE)
                 OR MATCH(production_companies, production_countries) AGAINST(:query IN BOOLEAN MODE))
            )
            SELECT
                m.*,
                (
                    CASE WHEN m.title = :exact_query             THEN 100 ELSE 0 END +
                    CASE WHEN m.original_title = :exact_query    THEN 95  ELSE 0 END +
                    CASE WHEN m.title LIKE CONCAT(:like_query, "%")        THEN 50 ELSE 0 END +
                    CASE WHEN m.original_title LIKE CONCAT(:like_query, "%") THEN 45 ELSE 0 END +
                    ft.text_score + 
                    CASE WHEN m.title LIKE CONCAT("%", :like_query, "%")   THEN 20 ELSE 0 END +
                    CASE WHEN m.original_title LIKE CONCAT("%", :like_query, "%") THEN 15 ELSE 0 END
                ) AS relevance_score
            FROM ft
            JOIN movies m ON m.id = ft.id
            WHERE ft.text_score > :min_score
        ';

        if ($filter->hasSort()) {
            $sortBy = $filter->getSortBy();
            $sortOrder = $filter->getSortOrder();
            $sql .= ' ORDER BY m.' . $this->convertCamelToSnakeCase($sortBy) . ' ' . $sortOrder . ' ';
        } else {
            $sql .= ' ORDER 
                    BY (LOWER(m.title) = LOWER(:exact_query_order_by)) DESC,
                       (LOWER(m.original_title) = LOWER(:exact_query_order_by)) DESC,
                       relevance_score DESC,
                       m.imdb_rating   DESC ';
        }
        $sql .= 'LIMIT :limit';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('query', $this->prepareSearchQuery($query));
        if (!$filter->hasSort()) {
            $stmt->bindValue('exact_query_order_by', trim($query));
        }
        $stmt->bindValue('exact_query', trim($query));
        $stmt->bindValue('like_query', trim($query));
        $stmt->bindValue('min_score', $minScore);
        $stmt->bindValue('limit', $limit, ParameterType::INTEGER);

        $result = $stmt->executeQuery();
        $movieData = $result->fetchAllAssociative();

        return $this->fetchMoviesByIds(array_column($movieData, 'id'));
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

        if ($filter->yearStart && $filter->yearEnd) {
            $qb->andWhere('m.releaseDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'))
                ->setParameter('endDate',   new \DateTimeImmutable($filter->yearEnd   . '-12-31'));

            $countQb->andWhere('m.releaseDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'))
                ->setParameter('endDate',   new \DateTimeImmutable($filter->yearEnd   . '-12-31'));
        } else {
            if ($filter->yearStart) {
                $qb->andWhere('m.releaseDate >= :startDate')
                    ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'));
                $countQb->andWhere('m.releaseDate >= :startDate')
                    ->setParameter('startDate', new \DateTimeImmutable($filter->yearStart . '-01-01'));
            }
            if ($filter->yearEnd) {
                $qb->andWhere('m.releaseDate <= :endDate')
                    ->setParameter('endDate', new \DateTimeImmutable($filter->yearEnd . '-12-31'));
                $countQb->andWhere('m.releaseDate <= :endDate')
                    ->setParameter('endDate', new \DateTimeImmutable($filter->yearEnd . '-12-31'));
            }
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

        $qb = $qb->setFirstResult($offset)
            ->setMaxResults($limit);
        if ($filter->hasSort()) {
            $sortBy = $filter->getSortBy();
            $sortOrder = $filter->getSortOrder();
            $qb = $qb->orderBy('m.' . $sortBy, $sortOrder);
        } else {
            $qb = $qb->orderBy('m.releaseDate', 'DESC');
        }

        $movies = $qb->getQuery()
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

    public function searchMoviesByCriteriaWithFuzzy(MovieFilter $filter, int $limit = 10, float $minScore = 0.1): array
    {
        $query = $filter->query;
        $sql = '
            WITH fuzzy_scores AS (
                SELECT DISTINCT
                    id,
                    title,
                    original_title,
                    -- Exact match scoring (highest priority)
                    CASE 
                        WHEN LOWER(title) = LOWER(:exact_query) THEN 100
                        WHEN LOWER(original_title) = LOWER(:exact_query) THEN 95
                        ELSE 0 
                    END as exact_score,
                    
                    -- Enhanced prefix matching with word boundary support
                    GREATEST(
                        CASE 
                            WHEN LOWER(title) LIKE LOWER(CONCAT(:like_query, "%")) THEN 50
                            WHEN LOWER(original_title) LIKE LOWER(CONCAT(:like_query, "%")) THEN 45
                            ELSE 0 
                        END,
                        -- Word-start matching (for "forr" matching "Forrest Gump")
                        CASE 
                            WHEN LENGTH(:clean_query) >= 3 AND LOWER(title) LIKE LOWER(CONCAT(:clean_query, "%")) THEN 60
                            WHEN LENGTH(:clean_query) >= 3 AND LOWER(title) LIKE LOWER(CONCAT("% ", :clean_query, "%")) THEN 50
                            WHEN LENGTH(:clean_query) >= 3 AND LOWER(original_title) LIKE LOWER(CONCAT(:clean_query, "%")) THEN 55
                            WHEN LENGTH(:clean_query) >= 3 AND LOWER(original_title) LIKE LOWER(CONCAT("% ", :clean_query, "%")) THEN 45
                            ELSE 0 
                        END
                    ) as prefix_score,
                    
                    -- Full-text search
                    COALESCE(MATCH(title, original_title, description) AGAINST(:query IN BOOLEAN MODE), 0) * 10 as fulltext_score,
                    
                    -- Fuzzy matching using length-based similarity and substring matching
                    GREATEST(
                        -- Substring similarity
                        CASE 
                            WHEN :clean_query != "" AND title != "" THEN
                                GREATEST(0, 30 - ABS(LENGTH(title) - LENGTH(:clean_query)) * 2) *
                                (LOCATE(LOWER(:clean_query), LOWER(title)) > 0 OR LOCATE(LOWER(title), LOWER(:clean_query)) > 0)
                            ELSE 0 
                        END,
                        -- Original title similarity
                        CASE 
                            WHEN :clean_query != "" AND original_title != "" THEN
                                GREATEST(0, 25 - ABS(LENGTH(original_title) - LENGTH(:clean_query)) * 2) *
                                (LOCATE(LOWER(:clean_query), LOWER(original_title)) > 0)
                            ELSE 0 
                        END
                    ) as fuzzy_score,
                    
                    -- SOUNDEX phonetic matching
                    CASE 
                        WHEN SOUNDEX(title) = SOUNDEX(:clean_query) THEN 25
                        WHEN SOUNDEX(original_title) = SOUNDEX(:clean_query) THEN 20
                        ELSE 0 
                    END as phonetic_score,
                    
                    -- Enhanced popularity scoring
                    (
                        -- Weighted rating considering vote count reliability  
                        (imdb_rating * LOG10(GREATEST(imdb_votes, 1) + 1)) * 3 +
                        -- Cultural significance bonus (9.0+ rating with 500k+ votes)
                        CASE WHEN imdb_rating >= 9.0 AND imdb_votes >= 500000 THEN 50 ELSE 0 END +
                        -- Classic movie bonus (8.5+ rating from before 2000)
                        CASE WHEN imdb_rating >= 8.5 AND YEAR(release_date) < 2000 THEN 30 ELSE 0 END +
                        -- Popular modern movies (8.0+ with 1M+ votes)
                        CASE WHEN imdb_rating >= 8.0 AND imdb_votes >= 1000000 THEN 25 ELSE 0 END
                    ) as popularity_boost
                    
                FROM movies
                WHERE 
                    -- Filter out movies without IMDB ratings
                    imdb_rating IS NOT NULL AND imdb_rating > 0 AND
                    -- Pre-filter to reduce dataset
                    (
                        LOWER(title) LIKE LOWER(CONCAT("%", :like_query, "%")) OR
                        LOWER(original_title) LIKE LOWER(CONCAT("%", :like_query, "%")) OR
                        MATCH(title, original_title, description) AGAINST(:query IN BOOLEAN MODE) OR
                        SOUNDEX(title) = SOUNDEX(:clean_query) OR
                        SOUNDEX(original_title) = SOUNDEX(:clean_query)
                    )
            )
            SELECT 
                m.*,
fs.exact_score,
fs.prefix_score,
fs.fulltext_score,
fs.fuzzy_score,
fs.phonetic_score,
fs.popularity_boost,
                
                fs.exact_score + fs.prefix_score + fs.fulltext_score + fs.fuzzy_score + fs.phonetic_score + fs.popularity_boost as total_score
            FROM fuzzy_scores fs
            JOIN movies m ON m.id = fs.id
            WHERE (fs.exact_score + fs.prefix_score + fs.fulltext_score + fs.fuzzy_score + fs.phonetic_score) >= :min_score
            ';

        if ($filter->hasSort()) {
            $sortBy = $filter->getSortBy();
            $sortOrder = $filter->getSortOrder();
            $sql .= ' ORDER BY m.' . $this->convertCamelToSnakeCase($sortBy) . ' ' . $sortOrder . ' ';
        } else {
            $sql .= ' ORDER BY total_score DESC, m.imdb_rating DESC ';
        }

        $sql .= 'LIMIT :limit';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $cleanQuery = preg_replace('/[^\w\s]/', '', trim($query));

        $stmt->bindValue('query', $this->prepareFuzzySearchQuery($query));
        $stmt->bindValue('exact_query', trim($query));
        $stmt->bindValue('like_query', trim($query));
        $stmt->bindValue('clean_query', $cleanQuery);
        $stmt->bindValue('min_score', $minScore);
        $stmt->bindValue('limit', $limit, ParameterType::INTEGER);

        $result = $stmt->executeQuery();
        $movieData = $result->fetchAllAssociative();
        
        return $this->fetchMoviesByIds(array_column($movieData, 'id'));
    }

    private function prepareFuzzySearchQuery(string $query): string
    {
        $query = trim($query);

        if (preg_match('/^"(.+)"$/', $query, $matches)) {
            return '"' . $matches[1] . '"';
        }

        $words = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) === 1) {
            // For fuzzy search, be more flexible with single words
            return $words[0] . '*';
        }

        // For multiple words, allow some to be optional for better fuzzy results
        $required = array_slice($words, 0, max(1, intval(count($words) * 0.7))); // 70% required
        $optional = array_slice($words, count($required));

        $queryParts = [];
        foreach ($required as $word) {
            $queryParts[] = '+' . $word . '*';
        }
        foreach ($optional as $word) {
            $queryParts[] = $word . '*'; // Optional words
        }

        return implode(' ', $queryParts);
    }

    private function fetchMoviesByIds(array $movieIds): array
    {
        if (empty($movieIds)) {
            return [];
        }

        $movies = $this->createQueryBuilder('m')
            ->where('m.id IN (:ids)')
            ->setParameter('ids', $movieIds)
            ->getQuery()
            ->getResult();

        // Preserve order from original IDs array
        $movieMap = [];
        foreach ($movies as $movie) {
            $movieMap[$movie->getId()] = $movie;
        }

        $orderedMovies = [];
        foreach ($movieIds as $id) {
            if (isset($movieMap[$id])) {
                $orderedMovies[] = $movieMap[$id];
            }
        }

        return $orderedMovies;
    }

    private function convertCamelToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }
}
