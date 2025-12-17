<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217144703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement ADD brand VARCHAR(255) DEFAULT NULL, ADD model VARCHAR(255) DEFAULT NULL, ADD year INT DEFAULT NULL, ADD mileage INT DEFAULT NULL, ADD fuel_type VARCHAR(50) DEFAULT NULL, ADD location VARCHAR(255) DEFAULT NULL, ADD main_photo VARCHAR(255) DEFAULT NULL, ADD photos JSON DEFAULT NULL, ADD rating NUMERIC(3, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP brand, DROP model, DROP year, DROP mileage, DROP fuel_type, DROP location, DROP main_photo, DROP photos, DROP rating');
    }
}
