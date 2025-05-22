<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020150123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tracker_history_dtc_vin (id SERIAL NOT NULL, device_id INT DEFAULT NULL, tracker_payload_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, code TEXT NOT NULL, data JSON DEFAULT NULL, is_nullable_data BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BDF66B394A4C7D4 ON tracker_history_dtc_vin (device_id)');
        $this->addSql('CREATE INDEX IDX_7BDF66B310DB296A ON tracker_history_dtc_vin (tracker_payload_id)');
        $this->addSql('CREATE INDEX IDX_7BDF66B3545317D1 ON tracker_history_dtc_vin (vehicle_id)');
        $this->addSql('CREATE INDEX tracker_history_dtc_vin_device_id_created_at_index ON tracker_history_dtc_vin (device_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_dtc_vin_device_id_occurred_at_index ON tracker_history_dtc_vin (device_id, occurred_at)');
        $this->addSql('CREATE INDEX tracker_history_dtc_vin_vehicle_id_created_at_index ON tracker_history_dtc_vin (vehicle_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_dtc_vin_vehicle_id_occurred_at_index ON tracker_history_dtc_vin (vehicle_id, occurred_at)');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ADD CONSTRAINT FK_7BDF66B394A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ADD CONSTRAINT FK_7BDF66B310DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ADD CONSTRAINT FK_7BDF66B3545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tracker_history_dtc_vin');
    }
}
