<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223141459 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE tracker_history_sensor (id SERIAL NOT NULL, device_id INT DEFAULT NULL, tracker_history_id INT DEFAULT NULL, tracker_payload_id INT DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F02CE63E94A4C7D4 ON tracker_history_sensor (device_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F02CE63EDFB8E61F ON tracker_history_sensor (tracker_history_id)');
        $this->addSql('CREATE INDEX IDX_F02CE63E10DB296A ON tracker_history_sensor (tracker_payload_id)');
        $this->addSql('CREATE INDEX tracker_history_sensor_device_id_created_at_index ON tracker_history_sensor (device_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_sensor_device_id_occurred_at_index ON tracker_history_sensor (device_id, occurred_at)');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63EDFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E10DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor ADD tracker_history_sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666AB724835D3 FOREIGN KEY (tracker_history_sensor_id) REFERENCES tracker_history_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F9666AB724835D3 ON device_sensor (tracker_history_sensor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666AB724835D3');
        $this->addSql('DROP TABLE tracker_history_sensor');
        $this->addSql('DROP INDEX IDX_6F9666AB724835D3');
        $this->addSql('ALTER TABLE device_sensor DROP tracker_history_sensor_id');
    }
}
