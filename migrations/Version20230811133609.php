<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230811133609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE device_camera_event_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A7F1A935E237E06 ON device_camera_event_type (name)');
        $this->addSql('CREATE TABLE device_camera_event (id BIGSERIAL NOT NULL, device_id INT DEFAULT NULL, device_vendor_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, tracker_history_id BIGINT DEFAULT NULL, team_id INT DEFAULT NULL, remote_id VARCHAR(255) DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_id INT DEFAULT NULL, remote_type VARCHAR(255) DEFAULT NULL, extra_data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_363B0BB1C54C8C93 ON device_camera_event (type_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB194A4C7D4 ON device_camera_event (device_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB1CDE3FAE2 ON device_camera_event (device_vendor_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB1545317D1 ON device_camera_event (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB1C3423909 ON device_camera_event (driver_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB1DFB8E61F ON device_camera_event (tracker_history_id)');
        $this->addSql('CREATE INDEX IDX_363B0BB1296CD8AE ON device_camera_event (team_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_363B0BB12A3E9C94CDE3FAE2 ON device_camera_event (remote_id, device_vendor_id)');
        $this->addSql('CREATE TABLE device_camera_event_file (id BIGSERIAL NOT NULL, event_id BIGINT NOT NULL, remote_id VARCHAR(255) DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, url TEXT DEFAULT NULL, camera_type SMALLINT DEFAULT NULL, file_type SMALLINT DEFAULT NULL, extra_data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A3E7BAA2A3E9C9471F7E88B ON device_camera_event_file (remote_id, event_id)');
        $this->addSql('CREATE INDEX IDX_8A3E7BAA71F7E88B ON device_camera_event_file (event_id)');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB194A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1CDE3FAE2 FOREIGN KEY (device_vendor_id) REFERENCES device_vendor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event ADD CONSTRAINT FK_363B0BB1C54C8C93 FOREIGN KEY (type_id) REFERENCES device_camera_event_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_camera_event_file ADD CONSTRAINT FK_8A3E7BAA71F7E88B FOREIGN KEY (event_id) REFERENCES device_camera_event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE device_camera_event_file');
        $this->addSql('DROP TABLE device_camera_event');
        $this->addSql('DROP TABLE device_camera_event_type');
    }
}
