<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210628232842 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_log ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX event_log_vehicle_id_index ON event_log (vehicle_id, event_id)');

        $this->updateVehicleDataP1();
        $this->updateVehicleDataP2();
        $this->updateVehicleDataP3();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP INDEX event_log_vehicle_id_index');
        $this->addSql('ALTER TABLE event_log DROP vehicle_id');
    }

    private function updateVehicleDataP1()
    {
        $this->addSql('UPDATE event_log
            SET vehicle_id = CAST(el_records.vehicle_id_value AS INT)
            FROM (SELECT el.id AS el_id, el.details ->> \'vehicleId\' AS vehicle_id_value
                  FROM event_log el
                  WHERE el.details ->> \'vehicleId\' IS NOT NULL) AS el_records
            WHERE event_log.id = el_records.el_id
                AND event_log.details ->> \'vehicleId\' IS NOT NULL;
        ');
    }

    private function updateVehicleDataP2()
    {
        $this->addSql('UPDATE event_log
            SET vehicle_id = CAST(el_records.vehicle_id_value AS INT)
            FROM (SELECT el.id AS el_id, el.details -> \'vehicle\' ->> \'id\' AS vehicle_id_value
                  FROM event_log el
                  WHERE el.details -> \'vehicle\' ->> \'id\' IS NOT NULL) AS el_records
            WHERE event_log.id = el_records.el_id
                AND event_log.details -> \'vehicle\' ->> \'id\' IS NOT NULL;
        ');
    }

    private function updateVehicleDataP3()
    {
        $this->addSql('UPDATE event_log
            SET vehicle_id = CAST(el_records.vehicle_id_value AS INT)
            FROM (SELECT el.id AS el_id, (el.details -> \'device\' -> \'deviceInstallation\' -> \'vehicle\' ->> \'id\')::int AS vehicle_id_value
                  FROM event_log el
                  WHERE el.details -> \'device\' -> \'deviceInstallation\' -> \'vehicle\' ->> \'id\' IS NOT NULL) AS el_records
            WHERE event_log.id = el_records.el_id
                AND event_log.details ->\'device\' -> \'deviceInstallation\' -> \'vehicle\' ->> \'id\' IS NOT NULL;
        ');
    }
}
