<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240731184808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, adult TINYINT(1) NOT NULL, also_known_as LONGTEXT DEFAULT NULL, biography LONGTEXT DEFAULT NULL, birthday DATE DEFAULT NULL, deathday DATE DEFAULT NULL, gender VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, tmdb_id INT DEFAULT NULL, imdb_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, profile_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie_actor (movie_id INT NOT NULL, actor_id INT NOT NULL, INDEX IDX_3A374C658F93B6FC (movie_id), INDEX IDX_3A374C6510DAF24A (actor_id), PRIMARY KEY(movie_id, actor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE movie_actor ADD CONSTRAINT FK_3A374C658F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_actor ADD CONSTRAINT FK_3A374C6510DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movies CHANGE title title TINYTEXT NOT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movie_genre MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON movie_genre');
        $this->addSql('ALTER TABLE movie_genre DROP id');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT FK_FD1229648F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT FK_FD1229644296D31F FOREIGN KEY (genre_id) REFERENCES genres (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FD1229648F93B6FC ON movie_genre (movie_id)');
        $this->addSql('CREATE INDEX IDX_FD1229644296D31F ON movie_genre (genre_id)');
        $this->addSql('ALTER TABLE movie_genre ADD PRIMARY KEY (movie_id, genre_id)');
        $this->addSql('ALTER TABLE tv_show_genre MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON tv_show_genre');
        $this->addSql('ALTER TABLE tv_show_genre DROP id');
        $this->addSql('ALTER TABLE tv_show_genre ADD CONSTRAINT FK_376362345E3A35BB FOREIGN KEY (tv_show_id) REFERENCES tv_shows (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tv_show_genre ADD CONSTRAINT FK_376362344296D31F FOREIGN KEY (genre_id) REFERENCES genres (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_376362345E3A35BB ON tv_show_genre (tv_show_id)');
        $this->addSql('CREATE INDEX IDX_376362344296D31F ON tv_show_genre (genre_id)');
        $this->addSql('ALTER TABLE tv_show_genre ADD PRIMARY KEY (tv_show_id, genre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_actor DROP FOREIGN KEY FK_3A374C658F93B6FC');
        $this->addSql('ALTER TABLE movie_actor DROP FOREIGN KEY FK_3A374C6510DAF24A');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE movie_actor');
        $this->addSql('ALTER TABLE tv_show_genre DROP FOREIGN KEY FK_376362345E3A35BB');
        $this->addSql('ALTER TABLE tv_show_genre DROP FOREIGN KEY FK_376362344296D31F');
        $this->addSql('DROP INDEX IDX_376362345E3A35BB ON tv_show_genre');
        $this->addSql('DROP INDEX IDX_376362344296D31F ON tv_show_genre');
        $this->addSql('ALTER TABLE tv_show_genre ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE movies CHANGE title title VARCHAR(255) NOT NULL, CHANGE duration duration VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie_genre DROP FOREIGN KEY FK_FD1229648F93B6FC');
        $this->addSql('ALTER TABLE movie_genre DROP FOREIGN KEY FK_FD1229644296D31F');
        $this->addSql('DROP INDEX IDX_FD1229648F93B6FC ON movie_genre');
        $this->addSql('DROP INDEX IDX_FD1229644296D31F ON movie_genre');
        $this->addSql('ALTER TABLE movie_genre ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
