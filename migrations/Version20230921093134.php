<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921093134 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX event_log_event_id_event_date_entity_team_id_index ON event_log (event_id, event_date, entity_team_id);');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX event_log_event_id_event_date_entity_team_id_index');
    }
}
