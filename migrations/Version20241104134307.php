<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104134307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor CHANGE adult adult TINYINT(1) DEFAULT NULL, CHANGE gender gender VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movies ADD imdb_id INT DEFAULT NULL, ADD imdb_rating VARCHAR(255) DEFAULT NULL, ADD imdb_votes VARCHAR(255) DEFAULT NULL, ADD original_title VARCHAR(255) DEFAULT NULL, ADD production_countries LONGTEXT DEFAULT NULL, ADD production_companies LONGTEXT DEFAULT NULL, ADD spoken_languages LONGTEXT DEFAULT NULL, ADD poster_path VARCHAR(255) DEFAULT NULL, CHANGE external_id tmdb_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor CHANGE adult adult TINYINT(1) NOT NULL, CHANGE gender gender VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE movies ADD external_id INT DEFAULT NULL, DROP tmdb_id, DROP imdb_id, DROP imdb_rating, DROP imdb_votes, DROP original_title, DROP production_countries, DROP production_companies, DROP spoken_languages, DROP poster_path');
    }
}
