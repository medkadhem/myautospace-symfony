<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219163906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update review service_id foreign key to CASCADE on delete';
    }

    public function up(Schema $schema): void
    {
        // Update the foreign key constraint to CASCADE on delete
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6ED5CA9E6');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Revert to original constraint without CASCADE
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6ED5CA9E6');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }
}
