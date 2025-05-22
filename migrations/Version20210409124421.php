<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210409124421 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE tracker_history_temp (id BIGSERIAL NOT NULL, device_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, tracker_history_id INT NOT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, movement BOOLEAN DEFAULT NULL, ignition BOOLEAN DEFAULT NULL, speed DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_calculated BOOLEAN DEFAULT \'false\' NOT NULL, is_calculated_idling BOOLEAN DEFAULT \'false\' NOT NULL, is_calculated_speeding BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E60D053794A4C7D4 ON tracker_history_temp (device_id)');
        $this->addSql('CREATE INDEX IDX_E60D0537545317D1 ON tracker_history_temp (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_E60D0537C3423909 ON tracker_history_temp (driver_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E60D0537DFB8E61F ON tracker_history_temp (tracker_history_id)');
        $this->addSql('CREATE INDEX tracker_history_temp_device_id_created_at_index ON tracker_history_temp (device_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_temp_device_id_ts_index ON tracker_history_temp (device_id, ts)');
        $this->addSql('CREATE INDEX tracker_history_temp_is_calculated_index ON tracker_history_temp (is_calculated)');
        $this->addSql('CREATE INDEX tracker_history_temp_is_calculated_idling_index ON tracker_history_temp (is_calculated_idling)');
        $this->addSql('CREATE INDEX tracker_history_temp_device_id_is_calculated_ts_index ON tracker_history_temp (device_id, is_calculated, ts)');
        $this->addSql('CREATE INDEX tracker_history_temp_device_id_is_calculated_speeding_ts_index ON tracker_history_temp (device_id, is_calculated_speeding, ts)');
        $this->addSql('CREATE INDEX tracker_history_temp_device_id_is_calculated_idling_ts_index ON tracker_history_temp (device_id, is_calculated_idling, ts)');
        $this->addSql('CREATE INDEX tracker_history_temp_is_calculated_speeding_index ON tracker_history_temp (is_calculated_speeding)');
        $this->addSql('ALTER TABLE tracker_history_temp ADD CONSTRAINT FK_E60D053794A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_temp ADD CONSTRAINT FK_E60D0537545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_temp ADD CONSTRAINT FK_E60D0537C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('ALTER TABLE tracker_history_temp ADD CONSTRAINT FK_E60D0537DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX tracker_history_device_id_is_calculated_index');
        $this->addSql('DROP INDEX tracker_history_is_calculated_idling_index');
        $this->addSql('DROP INDEX tracker_history_is_calculated_index');
        $this->addSql('DROP INDEX tracker_history_device_id_is_calculated_idling_index');
        $this->addSql('DROP INDEX tracker_history_is_calculated_speeding_index');
        $this->addSql('DROP INDEX tracker_history_device_id_ts_is_calculated_speeding_index');
        $this->addSql('DROP INDEX tracker_history_device_id_ts_is_calculated_idling_index');
        $this->addSql('DROP INDEX tracker_history_device_id_ts_is_calculated_index');
        $this->addSql('DROP INDEX tracker_history_device_id_is_calculated_speeding_index');

        $this->moveRecordsFromTrackerHistoryToTempTrackerHistory();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE tracker_history_temp');
        $this->addSql('CREATE INDEX tracker_history_device_id_is_calculated_index ON tracker_history (device_id, is_calculated)');
        $this->addSql('CREATE INDEX tracker_history_is_calculated_idling_index ON tracker_history (is_calculated_idling)');
        $this->addSql('CREATE INDEX tracker_history_is_calculated_index ON tracker_history (is_calculated)');
        $this->addSql('CREATE INDEX tracker_history_device_id_is_calculated_idling_index ON tracker_history (device_id, is_calculated_idling)');
        $this->addSql('CREATE INDEX tracker_history_is_calculated_speeding_index ON tracker_history (is_calculated_speeding)');
        $this->addSql('CREATE INDEX tracker_history_device_id_ts_is_calculated_speeding_index ON tracker_history (device_id, ts, is_calculated_speeding)');
        $this->addSql('CREATE INDEX tracker_history_device_id_ts_is_calculated_idling_index ON tracker_history (device_id, ts, is_calculated_idling)');
        $this->addSql('CREATE INDEX tracker_history_device_id_ts_is_calculated_index ON tracker_history (device_id, ts, is_calculated)');
        $this->addSql('CREATE INDEX tracker_history_device_id_is_calculated_speeding_index ON tracker_history (device_id, is_calculated_speeding)');
    }

    private function moveRecordsFromTrackerHistoryToTempTrackerHistory()
    {
        $this->addSql('INSERT INTO tracker_history_temp (id, device_id, vehicle_id, driver_id, tracker_history_id, ts, movement, ignition, speed, created_at, is_calculated, is_calculated_idling, is_calculated_speeding) SELECT (setval(\'tracker_history_temp_id_seq\',nextval(\'tracker_history_temp_id_seq\'))), device_id, vehicle_id, driver_id, id, ts, movement::BOOL, ignition::BOOL, speed, NOW(), is_calculated, is_calculated_idling, is_calculated_speeding FROM tracker_history WHERE (tracker_history.created_at >= NOW() - INTERVAL \'7\' DAY) AND (tracker_history.is_calculated = false OR tracker_history.is_calculated_idling = false OR tracker_history.is_calculated_speeding = false)');
    }

    private function updateRecordsFromTrackerHistory()
    {
        $this->addSql('UPDATE tracker_history SET is_calculated = true, is_calculated_idling = true, is_calculated_speeding = true WHERE (created_at >= NOW() - INTERVAL \'1\' MONTH) AND (is_calculated = false OR is_calculated_idling = false OR is_calculated_speeding = false)');
    }
}