<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217161324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD provider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A53A8AA FOREIGN KEY (provider_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_42C84955A53A8AA ON reservation (provider_id)');
        $this->addSql('ALTER TABLE review ADD reviewer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C670574616 FOREIGN KEY (reviewer_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_794381C670574616 ON review (reviewer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C670574616');
        $this->addSql('DROP INDEX IDX_794381C670574616 ON review');
        $this->addSql('ALTER TABLE review DROP reviewer_id');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A53A8AA');
        $this->addSql('DROP INDEX IDX_42C84955A53A8AA ON reservation');
        $this->addSql('ALTER TABLE reservation DROP provider_id');
    }
}
