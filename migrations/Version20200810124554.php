<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810124554 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX speeding_vehicle_id_started_at_finished_at_index ON speeding (vehicle_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX speeding_driver_id_started_at_finished_at_index ON speeding (driver_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX idling_vehicle_id_started_at_finished_at_index ON idling (vehicle_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX idling_driver_id_started_at_finished_at_index ON idling (driver_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX driving_behavior_vehicle_id_ts_index ON driving_behavior (vehicle_id, ts)');
        $this->addSql('CREATE INDEX driving_behavior_driver_id_ts_index ON driving_behavior (driver_id, ts)');
        $this->addSql('CREATE INDEX driving_behavior_device_id_ts_index ON driving_behavior (device_id, ts)');
        $this->addSql('CREATE INDEX device_installation_vehicle_id_installdate_uninstalldate_index ON device_installation (vehicle_id, installdate, uninstalldate)');
        $this->addSql('CREATE INDEX device_installation_device_id_installdate_uninstalldate_index ON device_installation (device_id, installdate, uninstalldate)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX device_installation_vehicle_id_installdate_uninstalldate_index');
        $this->addSql('DROP INDEX device_installation_device_id_installdate_uninstalldate_index');
        $this->addSql('DROP INDEX driving_behavior_vehicle_id_ts_index');
        $this->addSql('DROP INDEX driving_behavior_driver_id_ts_index');
        $this->addSql('DROP INDEX driving_behavior_device_id_ts_index');
        $this->addSql('DROP INDEX idling_vehicle_id_started_at_finished_at_index');
        $this->addSql('DROP INDEX idling_driver_id_started_at_finished_at_index');
        $this->addSql('DROP INDEX speeding_vehicle_id_started_at_finished_at_index');
        $this->addSql('DROP INDEX speeding_driver_id_started_at_finished_at_index');
    }
}
