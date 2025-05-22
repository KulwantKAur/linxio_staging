<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190916111448 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE tracker_history_last_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_history_last (id INT NOT NULL, tracker_payload_id INT DEFAULT NULL, device_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, tracker_history_id INT DEFAULT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, priority INT NOT NULL, lng NUMERIC(11, 8) NOT NULL, lat NUMERIC(11, 8) NOT NULL, alt DOUBLE PRECISION NOT NULL, angle DOUBLE PRECISION NOT NULL, satellites INT DEFAULT NULL, speed DOUBLE PRECISION DEFAULT NULL, movement INT DEFAULT NULL, ignition INT DEFAULT NULL, battery_voltage DOUBLE PRECISION DEFAULT NULL, external_voltage DOUBLE PRECISION DEFAULT NULL, temperature_level DOUBLE PRECISION DEFAULT NULL, engine_hours DOUBLE PRECISION DEFAULT NULL, gsm_signal INT DEFAULT NULL, odometer DOUBLE PRECISION DEFAULT NULL, ibutton VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A785295D10DB296A ON tracker_history_last (tracker_payload_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A785295D94A4C7D4 ON tracker_history_last (device_id)');
        $this->addSql('CREATE INDEX IDX_A785295D545317D1 ON tracker_history_last (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_A785295DC3423909 ON tracker_history_last (driver_id)');
        $this->addSql('CREATE INDEX IDX_A785295DDFB8E61F ON tracker_history_last (tracker_history_id)');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295D10DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295D94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295D545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295DC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295DDFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT fk_92fb68e57192664');
        $this->addSql('DROP INDEX uniq_92fb68e57192664');
        $this->addSql('ALTER TABLE device RENAME COLUMN last_tracker_history_id TO tracker_history_last_id');
        $this->addSql('UPDATE device SET tracker_history_last_id = NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EDBB19030 FOREIGN KEY (tracker_history_last_id) REFERENCES tracker_history_last (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92FB68EDBB19030 ON device (tracker_history_last_id)');
        $this->addSql('ALTER INDEX idx_c441c01e94a4c7d4 RENAME TO IDX_2C4207994A4C7D4');
        $this->addSql('ALTER INDEX idx_c441c01ef058a3f9 RENAME TO IDX_2C42079F058A3F9');
        $this->addSql('ALTER INDEX idx_c441c01e886802c5 RENAME TO IDX_2C42079886802C5');
        $this->addSql('ALTER INDEX idx_c441c01e545317d1 RENAME TO IDX_2C42079545317D1');
        $this->addSql('ALTER INDEX idx_c441c01ec3423909 RENAME TO IDX_2C42079C3423909');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68EDBB19030');
        $this->addSql('DROP SEQUENCE tracker_history_last_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_history_last');
        $this->addSql('DROP INDEX UNIQ_92FB68EDBB19030');
        $this->addSql('ALTER TABLE device RENAME COLUMN tracker_history_last_id TO last_tracker_history_id');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT fk_92fb68e57192664 FOREIGN KEY (last_tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_92fb68e57192664 ON device (last_tracker_history_id)');
        $this->addSql('ALTER INDEX idx_2c42079c3423909 RENAME TO idx_c441c01ec3423909');
        $this->addSql('ALTER INDEX idx_2c42079545317d1 RENAME TO idx_c441c01e545317d1');
        $this->addSql('ALTER INDEX idx_2c42079886802c5 RENAME TO idx_c441c01e886802c5');
        $this->addSql('ALTER INDEX idx_2c4207994a4c7d4 RENAME TO idx_c441c01e94a4c7d4');
        $this->addSql('ALTER INDEX idx_2c42079f058a3f9 RENAME TO idx_c441c01ef058a3f9');
    }
}
