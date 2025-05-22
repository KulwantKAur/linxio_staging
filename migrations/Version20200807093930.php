<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200807093930 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX route_device_id_started_at_idx ON route (device_id, started_at)');
        $this->addSql('CREATE INDEX route_driver_id_started_at_finished_at_idx ON route (driver_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX route_vehicle_id_started_at_finished_at_idx ON route (vehicle_id, started_at, finished_at)');
        $this->addSql('CREATE INDEX route_temp_device_id_started_at_idx ON route_temp (device_id, started_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX route_temp_device_id_started_at_idx');
        $this->addSql('DROP INDEX route_device_id_started_at_idx');
        $this->addSql('DROP INDEX route_driver_id_started_at_finished_at_idx');
        $this->addSql('DROP INDEX route_vehicle_id_started_at_finished_at_idx');
    }
}
