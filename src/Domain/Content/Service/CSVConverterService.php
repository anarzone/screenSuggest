<?php

namespace App\Domain\Content\Service;

ini_set('memory_limit', '-1');

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CSVConverterService
{
    private array $relationCaches;

    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
        $this->relationCaches = [];
    }

    public function importMovies(bool $forceReload = false): void
    {
        $entityManager = $this->managerRegistry->getManager('dataset');
        $connection = $entityManager->getConnection();

        $csvExists = $this->csvFileExists();

        if ($forceReload) {
            if (!$csvExists) {
                throw new NotFoundHttpException('CSV file not found and no data in staging table');
            }
            $this->loadCsvToStaging($connection);
        }

        $this->processFromStaging($connection);
    }

    /**
     * Check if CSV file exists
     */
    private function csvFileExists(): bool
    {
        $csvFile = $this->getCsvFilePath();
        return file_exists($csvFile);
    }

    /**
     * Get CSV file path
     */
    private function getCsvFilePath(): string
    {
        return dirname(__DIR__, 3) . '/Domain/Content/dataset/TMDB_all_movies.csv';
    }

    /**
     * Load CSV data into staging table
     */
    private function loadCsvToStaging(Connection $connection): void
    {
        $csvFile = $this->getCsvFilePath();

        echo "Loading CSV data into staging table..." . PHP_EOL;

        // Create or truncate the staging table
        $this->createOrTruncateStagingTable($connection);

        // Bulk load the CSV into the staging table
        $sql = sprintf(
            "LOAD DATA LOCAL INFILE '%s'
             INTO TABLE movies_staging
             CHARACTER SET utf8mb4
             FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
             LINES TERMINATED BY '\n'
             IGNORE 1 LINES
             (id, title, vote_average, vote_count, status, release_date, revenue, runtime, budget, imdb_id, original_language, original_title, overview, popularity, tagline, genres, production_companies, production_countries, spoken_languages, cast, director, director_of_photography, writers, producers, music_composer, imdb_rating, imdb_votes, poster_path)",
            addslashes($csvFile)
        );
        $connection->executeQuery($sql);

        echo "CSV data loaded into staging table successfully" . PHP_EOL;
    }

    /**
     * Process data from staging table to main tables
     */
    private function processFromStaging(Connection $connection): void
    {
        echo "Processing data from staging table..." . PHP_EOL;

        // Truncate target tables
        $this->truncateTargetTables($connection);

        // Process the staging records
        $connection->beginTransaction();
        try {
            $pageSize = 2000;
            $lastImdbId = 0;

            $relationTypes = [
                ['stagingRecordField' => 'genres', 'table' => 'genres', 'junctionTable' => 'movie_genre', 'junctionColumn' => 'genre_id'],
                ['stagingRecordField' => 'cast', 'table' => 'actors', 'junctionTable' => 'movie_actor', 'junctionColumn' => 'actor_id'],
                ['stagingRecordField' => 'director', 'table' => 'directors', 'junctionTable' => 'movie_director', 'junctionColumn' => 'director_id']
            ];

            // Pre-initialize relation caches
            foreach ($relationTypes as $relationType) {
                $entityTable = $relationType['table'];
                $this->relationCaches[$entityTable] = [];

                $existingEntitiesQuery = "SELECT id, name FROM $entityTable";
                $existingEntities = $connection->fetchAllAssociative($existingEntitiesQuery);

                foreach ($existingEntities as $entity) {
                    $this->relationCaches[$entityTable][$entity['name']] = $entity['id'];
                }
            }

            $totalCount = $connection->fetchOne("SELECT COUNT(*) FROM movies_staging");
            $processedCount = 0;

            while (true) {
                $query = "SELECT * FROM movies_staging WHERE id > ? ORDER BY id LIMIT " . $pageSize;
                $stagingRecords = $connection->fetchAllAssociative($query, [$lastImdbId]);

                if (empty($stagingRecords)) {
                    break;
                }

                $movieBatchData = [];
                $tmdbIds = [];

                foreach ($stagingRecords as $record) {
                    $lastImdbId = $record['id'];
                    $tmdbIds[] = $record['id'];

                    $movieBatchData[] = [
                        'title' => $this->truncateString($record['title'], 255),
                        'original_title' => $this->truncateString($record['original_title'], 255),
                        'description' => $record['overview'],
                        'release_date' => $this->validateAndReturnDate($record['release_date']),
                        'duration' => $record['runtime'],
                        'tmdb_id' => $record['id'],
                        'imdb_id' => $this->truncateString($record['imdb_id'], 50),
                        'imdb_rating' => $record['imdb_rating'],
                        'imdb_votes' => $record['imdb_votes'],
                        'production_countries' => $record['production_countries'],
                        'production_companies' => $record['production_companies'],
                        'spoken_languages' => $record['spoken_languages'],
                        'poster_path' => $this->truncateString($record['poster_path'], 255),
                    ];
                }

                $this->batchInsert($connection, 'movies', $movieBatchData);

                $processedCount += count($movieBatchData);
                $percentComplete = round(($processedCount / $totalCount) * 100, 2);
                echo "Processed " . count($movieBatchData) . " movies. Progress: $processedCount/$totalCount ($percentComplete%)" . PHP_EOL;

                $placeholders = implode(',', array_fill(0, count($tmdbIds), '?'));
                $idMappingQuery = "SELECT id, tmdb_id FROM movies WHERE tmdb_id IN ($placeholders)";
                $mappingResults = $connection->fetchAllAssociative($idMappingQuery, $tmdbIds);

                $movieIdMapping = [];
                foreach ($mappingResults as $result) {
                    $movieIdMapping[$result['tmdb_id']] = $result['id'];
                }

                foreach ($relationTypes as $relationType) {
                    $this->processRelationsForBatch(
                        $connection,
                        $stagingRecords,
                        $movieIdMapping,
                        $relationType
                    );
                }

                $connection->commit();
                $connection->beginTransaction();
            }

            $connection->commit();
            echo "Import completed successfully" . PHP_EOL;
        } catch (\Exception $e) {
            $connection->rollBack();
            echo "Error during import: " . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }

    // ... rest of your existing methods remain the same
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
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ";
        $connection->executeQuery($createTableSQL);
        $connection->executeQuery("TRUNCATE TABLE movies_staging");
    }

    private function batchInsert(Connection $connection, string $table, array $batchData): void
    {
        if (empty($batchData)) {
            return;
        }

        // For very large batches, split into smaller chunks to avoid query size limits
        $chunkSize = 500;
        $chunks = array_chunk($batchData, $chunkSize);

        foreach ($chunks as $chunk) {
            $columns = array_keys($chunk[0]);
            $params = [];
            $rowsPlaceholders = [];

            foreach ($chunk as $row) {
                $placeholders = [];
                foreach ($columns as $col) {
                    $placeholders[] = '?';
                    $params[] = $row[$col];
                }
                $rowsPlaceholders[] = '(' . implode(',', $placeholders) . ')';
            }

            $query = 'INSERT IGNORE INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES ' . implode(',', $rowsPlaceholders);
            $connection->executeQuery($query, $params);
        }
    }

    private function processRelationsForBatch(
        Connection $connection,
        array $stagingRecords,
        array $movieIdMapping,
        array $relationType
    ): void {
        $stagingRecordField = $relationType['stagingRecordField'];
        $entityTable = $relationType['table'];
        $junctionTable = $relationType['junctionTable'];
        $junctionColumn = $relationType['junctionColumn'];

        // Initialize relation cache for this entity type if not exists
        if (!isset($this->relationCaches[$entityTable])) {
            $this->relationCaches[$entityTable] = [];

            // Pre-load existing entities to avoid redundant inserts and queries
            $existingEntitiesQuery = "SELECT id, name FROM $entityTable";
            $existingEntities = $connection->fetchAllAssociative($existingEntitiesQuery);

            foreach ($existingEntities as $entity) {
                $this->relationCaches[$entityTable][$entity['name']] = $entity['id'];
            }
        }

        // Extract and collect all unique relation entities for this batch
        $newEntities = [];
        $relationMap = [];

        foreach ($stagingRecords as $record) {
            $tmdbId = $record['id'];
            if (!isset($movieIdMapping[$tmdbId]) || empty($record[$stagingRecordField])) {
                continue;
            }

            $movieId = $movieIdMapping[$tmdbId];
            $items = array_map('trim', explode(',', $record[$stagingRecordField]));

            // Use array_unique to remove any duplicate items in the source data
            $items = array_unique($items);

            foreach ($items as $item) {
                if (empty($item)) continue;

                // Check if this entity is not in our cache yet
                if (!isset($this->relationCaches[$entityTable][$item])) {
                    $newEntities[$item] = true;
                }

                if (!isset($relationMap[$movieId])) {
                    $relationMap[$movieId] = [];
                }
                $relationMap[$movieId][] = $item;
            }
        }

        // Only insert entities that don't already exist in our cache
        if (!empty($newEntities)) {
            $entityBatchData = [];
            foreach (array_keys($newEntities) as $name) {
                $entityBatchData[] = ['name' => $name];
            }

            if (!empty($entityBatchData)) {
                $this->batchInsert($connection, $entityTable, $entityBatchData);
            }

            // Get the IDs of the entities we just inserted
            $placeholders = str_repeat('?,', count($newEntities) - 1) . '?';
            $entityNames = array_keys($newEntities);
            $entityQuery = "SELECT id, name FROM $entityTable WHERE name IN ($placeholders)";
            $entityResults = $connection->fetchAllAssociative($entityQuery, $entityNames);

            // Update our cache with the new entities
            foreach ($entityResults as $result) {
                $this->relationCaches[$entityTable][$result['name']] = $result['id'];
            }
        }

        // Build and execute junction table inserts
        $junctionBatchData = [];
        foreach ($relationMap as $movieId => $entityNames) {
            foreach ($entityNames as $name) {
                if (isset($this->relationCaches[$entityTable][$name])) {
                    $junctionBatchData[] = [
                        'movie_id' => $movieId,
                        $junctionColumn => $this->relationCaches[$entityTable][$name]
                    ];
                }
            }
        }

        if (!empty($junctionBatchData)) {
            $this->batchInsert($connection, $junctionTable, $junctionBatchData);
        }
    }

    private function truncateTargetTables(Connection $connection): void
    {
        // Disable foreign key checks to allow truncating tables with relationships
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate junction tables first
        $connection->executeQuery('TRUNCATE TABLE movie_genre');
        $connection->executeQuery('TRUNCATE TABLE movie_actor');
        $connection->executeQuery('TRUNCATE TABLE movie_director');

        // Truncate main tables
        $connection->executeQuery('TRUNCATE TABLE movies');
        $connection->executeQuery('TRUNCATE TABLE genres');
        $connection->executeQuery('TRUNCATE TABLE actors');
        $connection->executeQuery('TRUNCATE TABLE directors');

        // Re-enable foreign key checks
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Validate and format a date string.
     * Returns the date in 'Y-m-d' format or null if the date is invalid.
     */
    private function validateAndReturnDate(?string $date): ?string
    {
        if ($date === null || trim($date) === '') {
            return null;
        }

        $date = trim($date);
        $exploded = explode('-', $date);

        // Check basic format
        if (count($exploded) !== 3) {
            return null;
        }

        // Check for negative years or years outside MySQL's supported range
        $year = (int)$exploded[0];
        if ($year <= 0 || $year > 9999) {
            return null;
        }

        // Create DateTime and verify it's valid
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            return null;
        }

        return $dateTime->format('Y-m-d');
    }

    private function truncateString(?string $string, int $length): ?string
    {
        if ($string === null) {
            return null;
        }

        $string = trim($string);

        // For multibyte characters, one character can use up to 4 bytes in UTF-8
        // So let's be more conservative with the length
        $truncated = mb_substr($string, 0, (int)($length * 0.75), 'UTF-8');

        // Make sure we're definitely within limits
        while (strlen($truncated) > $length) {
            $truncated = mb_substr($truncated, 0, mb_strlen($truncated, 'UTF-8') - 1, 'UTF-8');
        }

        return $truncated;
    }
}
