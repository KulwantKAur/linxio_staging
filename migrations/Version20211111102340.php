<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Tracker\TrackerHistoryTemp;
use App\Resources\procedures\CreatePartitions;
use App\Resources\procedures\DeletePartitions;
use App\Resources\procedures\UpdateRelatedDataByTrackerHistory;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @todo make changes in ClearTrackerHistoryTempCommand after release
 */
final class Version20211111102340 extends AbstractMigration
{
    private function copyDataToTrackerHistoryTempPartTrigger()
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION copy_data_to_tracker_history_temp_part() RETURNS trigger AS $res$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        DELETE FROM tracker_history_temp_part WHERE id = OLD.id;
    ELSIF (TG_OP = 'UPDATE') THEN
        DELETE FROM tracker_history_temp_part WHERE id = NEW.id;
        INSERT INTO tracker_history_temp_part SELECT NEW.*;
    ELSIF (TG_OP = 'INSERT') THEN
        INSERT INTO tracker_history_temp_part SELECT NEW.*;
    END IF;

    RETURN NEW;
END;
$res$ LANGUAGE plpgsql;
SQL;
    }

    public function getDescription(): string
    {
        return 'Create partitions for table "tracker_history_temp" and add auto-generation with pg_cron';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tracker_history_temp_part (id BIGSERIAL NOT NULL, device_id integer constraint fk_thtp_device_id references device on delete set null, vehicle_id integer constraint fk_thtp_vehicle_id references vehicle on delete set null, driver_id bigint constraint fk_thtp_driver_id references users on delete set null, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, movement BOOLEAN DEFAULT NULL, ignition BOOLEAN DEFAULT NULL, speed DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_calculated BOOLEAN DEFAULT \'false\' NOT NULL, is_calculated_idling BOOLEAN DEFAULT \'false\' NOT NULL, is_calculated_speeding BOOLEAN DEFAULT \'false\' NOT NULL, traccar_position_id bigint, team_id INT DEFAULT NULL constraint fk_thtp_team_id references team on delete SET NULL, tracker_history_id bigint not null) PARTITION BY RANGE (created_at)');
        $this->addSql('CREATE INDEX thtp_device_id_index ON tracker_history_temp_part (device_id)');
        $this->addSql('CREATE INDEX thtp_vehicle_id_index ON tracker_history_temp_part (vehicle_id)');
        $this->addSql('CREATE INDEX thtp_driver_id_index ON tracker_history_temp_part (driver_id)');
        $this->addSql('CREATE INDEX thtp_device_id_created_at_index ON tracker_history_temp_part (device_id, created_at)');
        $this->addSql('CREATE INDEX thtp_device_id_ts_index ON tracker_history_temp_part (device_id, ts)');
        $this->addSql('CREATE INDEX thtp_is_calculated_index ON tracker_history_temp_part (is_calculated)');
        $this->addSql('CREATE INDEX thtp_is_calculated_idling_index ON tracker_history_temp_part (is_calculated_idling)');
        $this->addSql('CREATE INDEX thtp_is_calculated_speeding_index ON tracker_history_temp_part (is_calculated_speeding)');
        $this->addSql('CREATE INDEX thtp_device_id_is_calculated_ts_index ON tracker_history_temp_part (device_id, is_calculated, ts)');
        $this->addSql('CREATE INDEX thtp_device_id_is_calculated_speeding_ts_index ON tracker_history_temp_part (device_id, is_calculated_speeding, ts)');
        $this->addSql('CREATE INDEX thtp_device_id_is_calculated_idling_ts_index ON tracker_history_temp_part (device_id, is_calculated_idling, ts)');
        $this->addSql('CREATE INDEX thtp_created_at_index ON tracker_history_temp_part (created_at)');
        $this->addSql('CREATE INDEX thtp_team_id_index ON tracker_history_temp_part (team_id)');
        $this->addSql('CREATE INDEX thtp_tracker_history_id_index ON tracker_history_temp_part (tracker_history_id)');

        $this->addSql('DROP FUNCTION IF EXISTS create_partitions(text, text, text, text, text, integer, integer);');
        $this->addSql(CreatePartitions::up());
        $this->addSql('SELECT create_partitions(\'tracker_history_temp_part\', \'thtp\', \'1 day\', \'day\', \'YYYY_MM_DD\', 1, -1, ARRAY[\'tracker_history_id\']);');
        $this->addSql($this->copyDataToTrackerHistoryTempPartTrigger());
        $this->addSql('CREATE TRIGGER copy_tracker_history_temp_to_partitions AFTER INSERT OR UPDATE OR DELETE ON tracker_history_temp FOR EACH ROW EXECUTE PROCEDURE copy_data_to_tracker_history_temp_part();');
        $this->addSql('DROP FUNCTION IF EXISTS delete_partitions(text, text, text, text, integer, integer);');
        $this->addSql(DeletePartitions::up());

        $this->addSql('SELECT cron.schedule(\'create_new_tracker_history_temp_partitions\', \'00 12 * * *\', $$SELECT create_partitions(\'tracker_history_temp_part\', \'thtp\', \'1 day\', \'day\', \'YYYY_MM_DD\', 3, 0, ARRAY[\'tracker_history_id\']);$$);');
        $this->addSql('SELECT cron.schedule(\'delete_old_tracker_history_temp_partitions\', \'05 12 * * *\', $$SELECT delete_partitions(\'tracker_history_temp_part\', \'thtp\', \'1 day\', \'YYYY_MM_DD\', 30, 30 - ' . TrackerHistoryTemp::RECORDS_DAYS_TTL . ');$$);');

        $this->addSql(UpdateRelatedDataByTrackerHistory::up());
        $this->addSql('CREATE TRIGGER update_related_data_by_tracker_history_trigger AFTER DELETE ON tracker_history FOR EACH ROW EXECUTE PROCEDURE update_related_data_by_tracker_history()');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS update_related_data_by_tracker_history_trigger ON tracker_history');
        $this->addSql('SELECT cron.unschedule(\'delete_old_tracker_history_temp_partitions\')');
        $this->addSql('SELECT cron.unschedule(\'create_new_tracker_history_temp_partitions\')');
        $this->addSql('DROP TRIGGER IF EXISTS copy_tracker_history_temp_to_partitions ON tracker_history_temp;');
        $this->addSql('DROP TABLE tracker_history_temp_part');
    }
}
