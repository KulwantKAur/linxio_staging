<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Service\Traccar\TraccarMigrationService;
use App\Service\Traccar\Traits\TraccarMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221110104210 extends AbstractMigration
{
    use TraccarMigrationTrait;

    private string $deletePositionsJobName = 'delete_old_traccar_positions';
    private string $deleteEventsJobName = 'delete_old_traccar_events';

    public function getDescription(): string
    {
        return 'Clear Traccar old data; Add Traccar indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT cron.schedule(\'' . $this->deletePositionsJobName . '\', \'*/5 9-21 * * *\', $$DELETE FROM tc_positions WHERE id IN (SELECT id FROM tc_positions WHERE fixTime < NOW() - INTERVAL \'7\' DAY AND id NOT IN (SELECT positionId FROM tc_devices WHERE positionid IS NOT NULL) LIMIT 100000);$$);');
        $this->addSql('UPDATE cron.job SET database = \'' . TraccarMigrationService::getTraccarDBName() . '\' WHERE jobname = \'' . $this->deletePositionsJobName . '\'');
        $this->addSql('SELECT cron.schedule(\'' . $this->deleteEventsJobName . '\', \'*/5 9-21 * * *\', $$DELETE FROM tc_events WHERE id IN (SELECT id FROM tc_events WHERE eventTime < NOW() - INTERVAL \'7\' DAY LIMIT 100000);$$);');
        $this->addSql('UPDATE cron.job SET database = \'' . TraccarMigrationService::getTraccarDBName(). '\' WHERE jobname = \'' . $this->deleteEventsJobName . '\'');

        $traccarSql = 'CREATE INDEX idx_positions_fixtime ON tc_positions (fixtime); CREATE INDEX idx_events_eventtime ON tc_events (eventtime); ALTER TABLE tc_positions ALTER id TYPE BIGINT; ALTER TABLE tc_events ALTER id TYPE BIGINT';
        $this->upTraccar($traccarSql);
    }

    public function down(Schema $schema): void
    {
        $traccarSql = 'DROP INDEX idx_positions_fixtime; DROP INDEX idx_events_eventtime; ALTER TABLE tc_positions ALTER id TYPE INT; ALTER TABLE tc_events ALTER id TYPE INT';
        $this->downTraccar($traccarSql);

        $this->addSql('SELECT cron.unschedule(\'' . $this->deleteEventsJobName . '\')');
        $this->addSql('SELECT cron.unschedule(\'' . $this->deletePositionsJobName . '\')');
    }
}
