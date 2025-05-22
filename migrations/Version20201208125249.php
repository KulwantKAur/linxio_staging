<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201208125249 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX route_vehicle_id_device_id_started_at_finished_at_driver_id_idx ON route (vehicle_id, device_id, started_at, finished_at, driver_id)');
        $this->addSql('CREATE INDEX route_temp_idling_report_idx ON route_temp (vehicle_id, device_id, started_at, finished_at, driver_id)');
        $this->addSql('CREATE INDEX route_temp_type_idx ON route_temp (type)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX route_temp_type_idx');
        $this->addSql('DROP INDEX route_temp_idling_report_idx');
        $this->addSql('DROP INDEX route_vehicle_id_device_id_started_at_finished_at_driver_id_idx');
    }
}
