<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190828100240 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE driver_history DROP CONSTRAINT fk_e245142b296cd8ae');
        $this->addSql('DROP INDEX idx_e245142b296cd8ae');
        $this->addSql('ALTER TABLE driver_history RENAME COLUMN team_id TO driver_id');
        $this->addSql('ALTER TABLE driver_history ADD CONSTRAINT FK_E245142BC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E245142BC3423909 ON driver_history (driver_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE driver_history DROP CONSTRAINT FK_E245142BC3423909');
        $this->addSql('DROP INDEX IDX_E245142BC3423909');
        $this->addSql('ALTER TABLE driver_history RENAME COLUMN driver_id TO team_id');
        $this->addSql('ALTER TABLE driver_history ADD CONSTRAINT fk_e245142b296cd8ae FOREIGN KEY (team_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e245142b296cd8ae ON driver_history (team_id)');
    }
}
