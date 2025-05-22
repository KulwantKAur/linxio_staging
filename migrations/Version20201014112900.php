<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\DeviceModel;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201014112900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history ADD battery_voltage_percentage DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD solar_charging_status BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ADD battery_voltage_percentage DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ADD solar_charging_status BOOLEAN DEFAULT NULL');
        $this->updateDeviceOptionFixWithSpeed();
        $this->updateTopflytechModelName();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->revertTopflytechModelName();
        $this->addSql('ALTER TABLE tracker_history DROP battery_voltage_percentage');
        $this->addSql('ALTER TABLE tracker_history DROP solar_charging_status');
        $this->addSql('ALTER TABLE tracker_history_last DROP battery_voltage_percentage');
        $this->addSql('ALTER TABLE tracker_history_last DROP solar_charging_status');
    }

    private function updateTopflytechModelName()
    {
        $this->addSql("UPDATE device_model SET name = '" . DeviceModel::TOPFLYTECH_TLP1_LF . "' WHERE name = 'TLP1'");
    }

    private function revertTopflytechModelName()
    {
        $this->addSql("UPDATE device_model SET name = 'TLP1' WHERE name = '" . DeviceModel::TOPFLYTECH_TLP1_LF . "'");
    }

    private function updateDeviceOptionFixWithSpeed()
    {
        $this->addSql("UPDATE device SET is_fix_with_speed = true
            FROM (SELECT dm.id AS id
                  FROM device_model dm
                  WHERE dm.name = 'TLP1'
            ) AS model_sub
            WHERE device.model_id = model_sub.id");
    }
}
