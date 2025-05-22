<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211105122340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE driving_behavior ADD tracker_payload_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B378810DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E08B378810DB296A ON driving_behavior (tracker_payload_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT FK_E08B378810DB296A');
        $this->addSql('DROP INDEX IDX_E08B378810DB296A');
        $this->addSql('ALTER TABLE driving_behavior DROP tracker_payload_id');
    }
}
