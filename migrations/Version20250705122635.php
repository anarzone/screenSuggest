<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250704000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add full-text search indexes for movies, genres, actors, and directors tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE movies ADD FULLTEXT ft_movies_main (title, original_title, description)');
        $this->addSql('ALTER TABLE movies ADD FULLTEXT ft_movies_companies (production_companies, production_countries)');

        $this->addSql('ALTER TABLE actors ADD FULLTEXT ft_actors_main (name, biography, also_known_as)');

        $this->addSql('ALTER TABLE genres ADD FULLTEXT ft_genres (name)');

        $this->addSql('ALTER TABLE directors ADD FULLTEXT ft_directors (name)');
    }

    public function down(Schema $schema): void
    {
        // Remove full-text indexes
        $this->addSql('ALTER TABLE movies DROP INDEX ft_movies_main');
        $this->addSql('ALTER TABLE movies DROP INDEX ft_movies_companies');
        $this->addSql('ALTER TABLE actors DROP INDEX ft_actors_main');
        $this->addSql('ALTER TABLE genre DROP INDEX ft_genres');
        $this->addSql('ALTER TABLE director DROP INDEX ft_directors');
    }
}
