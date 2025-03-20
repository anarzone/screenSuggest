<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250309184852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE movie_director (movie_id INT NOT NULL, director_id INT NOT NULL, INDEX IDX_C266487D8F93B6FC (movie_id), INDEX IDX_C266487D899FB366 (director_id), PRIMARY KEY(movie_id, director_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE movie_director ADD CONSTRAINT FK_C266487D8F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_director ADD CONSTRAINT FK_C266487D899FB366 FOREIGN KEY (director_id) REFERENCES directors (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_actors_name ON actors');
        $this->addSql('DROP INDEX idx_genres_name ON genres');
        $this->addSql('ALTER TABLE movies DROP FOREIGN KEY FK_C61EED30899FB366');
        $this->addSql('DROP INDEX IDX_C61EED30899FB366 ON movies');
        $this->addSql('ALTER TABLE movies DROP director_id');
        $this->addSql('DROP INDEX idx_movie_actor_movie_id ON movie_actor');
        $this->addSql('DROP INDEX idx_movie_actor_actor_id ON movie_actor');
        $this->addSql('DROP INDEX idx_movie_genre_genre_id ON movie_genre');
        $this->addSql('DROP INDEX idx_movie_genre_movie_id ON movie_genre');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_director DROP FOREIGN KEY FK_C266487D8F93B6FC');
        $this->addSql('ALTER TABLE movie_director DROP FOREIGN KEY FK_C266487D899FB366');
        $this->addSql('DROP TABLE movie_director');
        $this->addSql('CREATE INDEX idx_actors_name ON actors (name)');
        $this->addSql('ALTER TABLE movies ADD director_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movies ADD CONSTRAINT FK_C61EED30899FB366 FOREIGN KEY (director_id) REFERENCES directors (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C61EED30899FB366 ON movies (director_id)');
        $this->addSql('CREATE INDEX idx_movie_genre_genre_id ON movie_genre (genre_id)');
        $this->addSql('CREATE INDEX idx_movie_genre_movie_id ON movie_genre (movie_id)');
        $this->addSql('CREATE INDEX idx_movie_actor_movie_id ON movie_actor (movie_id)');
        $this->addSql('CREATE INDEX idx_movie_actor_actor_id ON movie_actor (actor_id)');
        $this->addSql('CREATE INDEX idx_genres_name ON genres (name)');
    }
}
