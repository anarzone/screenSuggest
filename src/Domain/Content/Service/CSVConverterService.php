<?php

namespace App\Domain\Content\Service;

ini_set('memory_limit', '-1');

use App\Domain\Content\Hydrator\MovieHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class  CSVConverterService
{
    /**
     * Caches to avoid duplicate lookups for relations.
     *
     * Structure:
     * [
     *   'genres' => [ 'Action' => 1, 'Drama' => 2, ... ],
     *   'actors' => [ 'John Doe' => 5, ... ],
     *   // etc.
     * ]
     */
    private array $relationCaches;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private MovieService    $movieService,
        private MovieHydrator   $movieHydrator,
    )
    {
        $this->relationCaches = [];
    }

    /**
     * Bulk import movies using a staging table approach.
     *
     * This method does the following:
     * 1. Creates (or truncates) the staging table.
     * 2. Uses LOAD DATA LOCAL INFILE to quickly import the CSV into the staging table.
     * 3. Processes each staging record:
     *    - Inserts a new record into the main movies table.
     *    - Parses and inserts related values (genres, production companies, etc.)
     *       into the respective relation tables and junction tables.
     */
    public function bulkImportMoviesWithRelations(): void
    {
        $csvFile = dirname(__DIR__, 3) . '/Domain/Content/dataset/TMDB_all_movies.csv';

        if (!file_exists($csvFile)) {
            throw new NotFoundHttpException('CSV file not found');
        }

        $entityManager = $this->managerRegistry->getManager('dataset');
        $connection = $entityManager->getConnection();

        // Step 1: Create or truncate the staging table.
//        $this->createOrTruncateStagingTable($connection);
//
//        // Step 2: Bulk load the CSV into the staging table.
//        $sql = sprintf(
//            "LOAD DATA LOCAL INFILE '%s'
//             INTO TABLE movies_staging
//             FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
//             LINES TERMINATED BY '\n'
//             IGNORE 1 LINES
//             (id, title, vote_average, vote_count, status, release_date, revenue, runtime, budget, imdb_id, original_language, original_title, overview, popularity, tagline, genres, production_companies, production_countries, spoken_languages, cast, director, director_of_photography, writers, producers, music_composer, imdb_rating, imdb_votes, poster_path)",
//            addslashes($csvFile)
//        );
//        $connection->executeQuery($sql);

        // Step 3: Process the staging records.
        // Start a transaction to ensure data integrity.
        $connection->beginTransaction();
        try {
            $pageSize = 1000;
            $lastId = 0;

            while (true) {

                $query = "SELECT * FROM movies_staging WHERE id > ? ORDER BY id LIMIT " . $pageSize;
                $stagingRecords = $connection->fetchAllAssociative($query, [$lastId]);

                if (empty($stagingRecords)) {
                    break;
                }

                foreach ($stagingRecords as $record) {
                    $movieData = [
                        'tmdbId' => $record['id'],
                        'title' => $record['title'],
                        'description' => $record['overview'],
                        'duration' => $record['runtime'],
                        'releaseDate' => $this->validateAndReturnDate($record['release_date']),
                        'imdbId' => $record['imdb_id'],
                        'originalTitle' => $record['original_title'],
                        'imdbRating' => $record['imdb_rating'],
                        'imdbVotes' => $record['imdb_votes'],
                        'productionCountries' => $record['production_countries'],
                        'productionCompanies' => $record['production_companies'],
                        'spokenLanguages' => $record['spoken_languages'],
                        'directors' => $record['director'],
                        'genres' => $record['genres'],
                        'actors' => $record['cast'],


                        'poster_path' => $record['poster_path'],
                    ];

                    $movieDto = $this->movieHydrator->hydrate(movieData: $movieData);


                    $connection->insert('movies', $movieData);
                    $movieId = $connection->lastInsertId();

                    // Process many-to-many relationships.
                    $this->processManyToMany($connection, $movieId, $record['genres'], 'genres', 'movie_genre', 'genre_id');
                    $this->processManyToMany($connection, $movieId, $record['director'], 'directors', 'movie_director', 'director_id');
                    $this->processManyToMany($connection, $movieId, $record['writers'], 'writers', 'movie_writer', 'writer_id');
                    $this->processManyToMany($connection, $movieId, $record['producers'], 'producers', 'movie_producer', 'producer_id');
                    $this->processManyToMany($connection, $movieId, $record['music_composer'], 'music_composers', 'movie_music_composer', 'music_composer_id');
                }
            }

            $connection->commit();
            echo "Bulk import with relations completed" . PHP_EOL;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Create the staging table if it does not exist; if it exists, truncate it.
     *
     * @param Connection $connection
     */
    private function createOrTruncateStagingTable(Connection $connection): void
    {
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS movies_staging (
                id integer primary key,
                title VARCHAR(255),
                vote_average DECIMAL(3,1) DEFAULT NULL,
                vote_count INT DEFAULT NULL,
                status VARCHAR(50) DEFAULT NULL,
                release_date DATE DEFAULT NULL,
                revenue BIGINT DEFAULT NULL,
                runtime INT DEFAULT NULL,
                budget BIGINT DEFAULT NULL,
                imdb_id VARCHAR(50) DEFAULT NULL,
                original_language VARCHAR(10) DEFAULT NULL,
                original_title VARCHAR(255) DEFAULT NULL,
                overview TEXT,
                popularity DECIMAL(5,2) DEFAULT NULL,
                tagline VARCHAR(255) DEFAULT NULL,
                genres TEXT,
                production_companies TEXT,
                production_countries TEXT,
                spoken_languages TEXT,
                cast TEXT,
                director TEXT,
                director_of_photography TEXT,
                writers TEXT,
                producers TEXT,
                music_composer TEXT,
                imdb_rating DECIMAL(3,1) DEFAULT NULL,
                imdb_votes INT DEFAULT NULL,
                poster_path VARCHAR(255) DEFAULT NULL
            )
        ";
        $connection->executeQuery($createTableSQL);
        $connection->executeQuery("TRUNCATE TABLE movies_staging");
    }

    /**
     * Process a many-to-many relationship field.
     *
     * @param Connection $connection
     * @param int $movieId The ID of the movie.
     * @param string|null $data Comma-separated list of related names.
     * @param string $relationTable The table storing the related entities (e.g., 'genres', 'actors').
     * @param string $junctionTable The join table linking movies and the related table.
     * @param string $junctionColumn The column in the join table referencing the related entity (e.g., 'genre_id').
     */
    private function processManyToMany(Connection $connection, int $movieId, ?string $data, string $relationTable, string $junctionTable, string $junctionColumn): void
    {
        if (empty($data)) {
            return;
        }

        $items = explode(',', $data);
        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item)) {
                continue;
            }

            $relatedId = $this->getOrCreateEntity($connection, $relationTable, $item);

            // Check if the relationship already exists.
            $exists = $connection->fetchOne(
                "SELECT 1 FROM $junctionTable WHERE movie_id = ? AND $junctionColumn = ?",
                [$movieId, $relatedId]
            );
            if (!$exists) {
                $connection->insert($junctionTable, [
                    'movie_id' => $movieId,
                    $junctionColumn => $relatedId,
                ]);
            }
        }
    }

    /**
     * Get an entity ID from a relation table by name, or create it if it doesn't exist.
     *
     * @param Connection $connection
     * @param string $table The relation table (e.g., 'genres', 'actors').
     * @param string $name The name of the related entity.
     * @return int The ID of the entity.
     */
    private function getOrCreateEntity(Connection $connection, string $table, string $name): int
    {
        if (!isset($this->relationCaches[$table])) {
            $this->relationCaches[$table] = [];
        }
        if (isset($this->relationCaches[$table][$name])) {
            return $this->relationCaches[$table][$name];
        }

        $existing = $connection->fetchAssociative("SELECT id FROM $table WHERE name = ?", [$name]);
        if ($existing) {
            $id = $existing['id'];
        } else {
            $connection->insert($table, ['name' => $name]);
            $id = $connection->lastInsertId();
        }
        $this->relationCaches[$table][$name] = $id;
        return $id;
    }

    /**
     * Validate and format a date string.
     *
     * Returns the date in 'Y-m-d' format or null if the date is invalid.
     *
     * @param string $date
     * @return string|null
     */
    private function validateAndReturnDate(string $date): ?string
    {
        $date = trim($date);
        if (empty($date)) {
            return null;
        }
        $exploded = explode('-', $date);
        if (count($exploded) !== 3) {
            return null;
        }
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ? $dateTime->format('Y-m-d') : null;
    }
}
