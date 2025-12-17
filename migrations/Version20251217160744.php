<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217160744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD price NUMERIC(10, 3) DEFAULT NULL, ADD rating SMALLINT DEFAULT NULL, ADD comment LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD announcement_id INT DEFAULT NULL, CHANGE start_time start_time TIME DEFAULT NULL, CHANGE end_time end_time TIME DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955913AEA17 FOREIGN KEY (announcement_id) REFERENCES announcement (id)');
        $this->addSql('CREATE INDEX IDX_42C84955913AEA17 ON reservation (announcement_id)');
        $this->addSql('ALTER TABLE user ADD user_type VARCHAR(50) DEFAULT NULL, ADD first_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) DEFAULT NULL, ADD company_name VARCHAR(255) DEFAULT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955913AEA17');
        $this->addSql('DROP INDEX IDX_42C84955913AEA17 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP price, DROP rating, DROP comment, DROP created_at, DROP announcement_id, CHANGE start_time start_time TIME NOT NULL, CHANGE end_time end_time TIME NOT NULL, CHANGE service_id service_id INT NOT NULL');
        $this->addSql('ALTER TABLE `user` DROP user_type, DROP first_name, DROP last_name, DROP company_name, DROP rating');
    }
}
