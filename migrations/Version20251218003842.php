<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218003842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, announcement_id INT NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FCD53EDB6 (receiver_id), INDEX IDX_B6BD307F913AEA17 (announcement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, amount DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, counter_amount DOUBLE PRECISION DEFAULT NULL, counter_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, responded_at DATETIME DEFAULT NULL, buyer_id INT NOT NULL, announcement_id INT NOT NULL, INDEX IDX_29D6873E6C755722 (buyer_id), INDEX IDX_29D6873E913AEA17 (announcement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F913AEA17 FOREIGN KEY (announcement_id) REFERENCES announcement (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E6C755722 FOREIGN KEY (buyer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E913AEA17 FOREIGN KEY (announcement_id) REFERENCES announcement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F913AEA17');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E6C755722');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E913AEA17');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE offer');
    }
}
