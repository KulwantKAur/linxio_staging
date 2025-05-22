<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220701182230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs;
        $this->addSql('ALTER TABLE event_log ADD device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD driver_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD short_details JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD entity_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD entity_team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD team_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ADD user_by INT DEFAULT NULL');
        $this->addSql('CREATE INDEX event_log_entity_id_index ON event_log (entity_id, event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX event_log_entity_id_index');
        $this->addSql('ALTER TABLE event_log DROP device_id');
        $this->addSql('ALTER TABLE event_log DROP driver_id');
        $this->addSql('ALTER TABLE event_log DROP short_details');
        $this->addSql('ALTER TABLE event_log DROP entity_id');
        $this->addSql('ALTER TABLE event_log DROP entity_team_id');
        $this->addSql('ALTER TABLE event_log DROP team_by');
        $this->addSql('ALTER TABLE event_log DROP user_by');
    }
}
