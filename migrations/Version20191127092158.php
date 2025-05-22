<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127092158 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_643F1E575E237E06 ON reminder_category (name)');
        $this->addSql('ALTER TABLE reminder_category DROP CONSTRAINT fk_643f1e57296cd8ae');
        $this->addSql('DROP INDEX idx_643f1e57296cd8ae');
        $this->addSql('ALTER TABLE reminder_category DROP team_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_643F1E575E237E06');
        $this->addSql('ALTER TABLE reminder_category ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder_category ADD CONSTRAINT fk_643f1e57296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_643f1e57296cd8ae ON reminder_category (team_id)');
    }
}
