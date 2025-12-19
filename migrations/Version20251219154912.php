<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219154912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Make address.owner_id nullable and add CASCADE delete
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F817E3C61F9');
        $this->addSql('ALTER TABLE address CHANGE owner_id owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F817E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Revert address.owner_id to NOT NULL
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F817E3C61F9');
        $this->addSql('ALTER TABLE address CHANGE owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F817E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }
}
