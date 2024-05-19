<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240514170254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contents (id INT AUTO_INCREMENT NOT NULL, type TINYTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE creators (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, birthdate DATE DEFAULT NULL, nationality VARCHAR(70) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE directors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, nationality VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE genres (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie_genre (id INT AUTO_INCREMENT NOT NULL, movie_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movies (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, release_date DATE DEFAULT NULL, duration VARCHAR(255) DEFAULT NULL, director_id INT DEFAULT NULL, INDEX IDX_C61EED30899FB366 (director_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, rating INT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, movie_id INT DEFAULT NULL, INDEX IDX_6970EB0FA76ED395 (user_id), INDEX IDX_6970EB0F8F93B6FC (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE search_history (id INT AUTO_INCREMENT NOT NULL, search_query LONGTEXT DEFAULT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_AA6B9FD19D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tv_show_genre (id INT AUTO_INCREMENT NOT NULL, tv_show_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tv_shows (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, release_date DATE DEFAULT NULL, duration INT DEFAULT NULL, episodes_count INT DEFAULT NULL, creator_id INT DEFAULT NULL, INDEX IDX_9541196961220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_content_recommendations (id INT AUTO_INCREMENT NOT NULL, recommended_at DATETIME DEFAULT NULL, user_id_id INT NOT NULL, content_id_id INT DEFAULT NULL, INDEX IDX_4B951AB19D86650F (user_id_id), INDEX IDX_4B951AB19487CA85 (content_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE movies ADD CONSTRAINT FK_C61EED30899FB366 FOREIGN KEY (director_id) REFERENCES directors (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F8F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id)');
        $this->addSql('ALTER TABLE search_history ADD CONSTRAINT FK_AA6B9FD19D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE tv_shows ADD CONSTRAINT FK_9541196961220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id)');
        $this->addSql('ALTER TABLE user_content_recommendations ADD CONSTRAINT FK_4B951AB19D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_content_recommendations ADD CONSTRAINT FK_4B951AB19487CA85 FOREIGN KEY (content_id_id) REFERENCES contents (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movies DROP FOREIGN KEY FK_C61EED30899FB366');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FA76ED395');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F8F93B6FC');
        $this->addSql('ALTER TABLE search_history DROP FOREIGN KEY FK_AA6B9FD19D86650F');
        $this->addSql('ALTER TABLE tv_shows DROP FOREIGN KEY FK_9541196961220EA6');
        $this->addSql('ALTER TABLE user_content_recommendations DROP FOREIGN KEY FK_4B951AB19D86650F');
        $this->addSql('ALTER TABLE user_content_recommendations DROP FOREIGN KEY FK_4B951AB19487CA85');
        $this->addSql('DROP TABLE contents');
        $this->addSql('DROP TABLE creators');
        $this->addSql('DROP TABLE directors');
        $this->addSql('DROP TABLE genres');
        $this->addSql('DROP TABLE movie_genre');
        $this->addSql('DROP TABLE movies');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE search_history');
        $this->addSql('DROP TABLE tv_show_genre');
        $this->addSql('DROP TABLE tv_shows');
        $this->addSql('DROP TABLE user_content_recommendations');
        $this->addSql('DROP TABLE users');
    }
}
