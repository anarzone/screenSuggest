<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112193756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_genres_name ON genres (name)');
        $this->addSql('CREATE INDEX idx_actors_name ON actors (name)');
        $this->addSql('CREATE INDEX idx_movie_actor_movie_id ON movie_actor (movie_id)');
        $this->addSql('CREATE INDEX idx_movie_actor_actor_id ON movie_actor (actor_id)');
        $this->addSql('CREATE INDEX idx_movie_genre_movie_id ON movie_genre (movie_id)');
        $this->addSql('CREATE INDEX idx_movie_genre_genre_id ON movie_genre (genre_id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
