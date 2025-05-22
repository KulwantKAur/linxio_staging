<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210806145246 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device ADD parser_type SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD traccar_device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_model ADD parser_type SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE TABLE traccar_event_history (id BIGSERIAL NOT NULL, device_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, payload_id INT DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, traccar_event_id BIGINT DEFAULT NULL, traccar_position_id BIGINT DEFAULT NULL, traccar_geofence_id BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A9502BB94A4C7D4 ON traccar_event_history (device_id)');
        $this->addSql('CREATE INDEX IDX_A9502BB545317D1 ON traccar_event_history (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_A9502BBC3423909 ON traccar_event_history (driver_id)');
        $this->addSql('CREATE INDEX IDX_A9502BBD1664B27 ON traccar_event_history (payload_id)');
        $this->addSql('ALTER TABLE traccar_event_history ADD CONSTRAINT FK_A9502BB94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traccar_event_history ADD CONSTRAINT FK_A9502BB545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traccar_event_history ADD CONSTRAINT FK_A9502BBC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traccar_event_history ADD CONSTRAINT FK_A9502BBD1664B27 FOREIGN KEY (payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX traccar_event_history_device_id_occurred_at_index ON traccar_event_history (device_id, occurred_at)');
        $this->addSql('ALTER TABLE tracker_history_last ADD traccar_position_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_temp ADD traccar_position_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD traccar_position_id BIGINT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE traccar_event_history');
        $this->addSql('ALTER TABLE tracker_history DROP traccar_position_id');
        $this->addSql('ALTER TABLE tracker_history_temp DROP traccar_position_id');
        $this->addSql('ALTER TABLE tracker_history_last DROP traccar_position_id');
        $this->addSql('ALTER TABLE device_model DROP parser_type');
        $this->addSql('ALTER TABLE device DROP traccar_device_id');
        $this->addSql('ALTER TABLE device DROP parser_type');
    }
}
