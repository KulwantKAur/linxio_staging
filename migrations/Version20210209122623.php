<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209122623 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666AB94A4C7D4');
        $this->addSql('ALTER TABLE device_sensor ALTER device_id SET NOT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666AB94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT FK_F02CE63E99A78D8E');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER device_sensor_id SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E99A78D8E FOREIGN KEY (device_sensor_id) REFERENCES device_sensor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE SEQUENCE sensor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sensor (id INT NOT NULL, type_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, sensor_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC8617B0A247991F ON sensor (sensor_id)');
        $this->addSql('CREATE INDEX IDX_BC8617B0C54C8C93 ON sensor (type_id)');
        $this->addSql('CREATE INDEX IDX_BC8617B0DE12AB56 ON sensor (created_by)');
        $this->addSql('CREATE INDEX IDX_BC8617B016FE72E1 ON sensor (updated_by)');
        $this->addSql('ALTER TABLE sensor ADD CONSTRAINT FK_BC8617B0C54C8C93 FOREIGN KEY (type_id) REFERENCES device_sensor_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sensor ADD CONSTRAINT FK_BC8617B0DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sensor ADD CONSTRAINT FK_BC8617B016FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->createSensorsFromDeviceSensors();
        $this->updateDeviceSensorsWithNewSensorIds();

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT fk_6f9666abc54c8c93');
        $this->addSql('DROP INDEX idx_6f9666abc54c8c93');
        $this->addSql('ALTER TABLE device_sensor DROP type_id');
        $this->addSql('ALTER TABLE device_sensor DROP label');
        $this->addSql('ALTER TABLE device_sensor ALTER sensor_id TYPE INT USING (sensor_id::integer)');
        $this->addSql('ALTER TABLE device_sensor ALTER sensor_id DROP DEFAULT');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F9666ABA247991F ON device_sensor (sensor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666ABA247991F');
        $this->addSql('DROP INDEX IDX_6F9666ABA247991F');
        $this->addSql('ALTER TABLE device_sensor ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ALTER sensor_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE device_sensor ALTER sensor_id DROP DEFAULT');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT fk_6f9666abc54c8c93 FOREIGN KEY (type_id) REFERENCES device_sensor_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6f9666abc54c8c93 ON device_sensor (type_id)');
        $this->addSql('DROP SEQUENCE sensor_id_seq CASCADE');
        $this->addSql('DROP TABLE sensor');

        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT fk_f02ce63e99a78d8e');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER device_sensor_id DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT fk_f02ce63e99a78d8e FOREIGN KEY (device_sensor_id) REFERENCES device_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT fk_6f9666ab94a4c7d4');
        $this->addSql('ALTER TABLE device_sensor ALTER device_id DROP NOT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT fk_6f9666ab94a4c7d4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function createSensorsFromDeviceSensors()
    {
        $this->addSql('INSERT INTO sensor (id, type_id, created_by, updated_by, sensor_id, created_at, updated_at, label) SELECT (setval(\'sensor_id_seq\', nextval(\'sensor_id_seq\'))), ds.type_id, ds.created_by, null, ds.sensor_id, ds.created_at, null, ds.label FROM device_sensor ds GROUP BY ds.sensor_id, ds.type_id, ds.created_by, ds.created_at, ds.label');
    }

    private function updateDeviceSensorsWithNewSensorIds()
    {
        $this->addSql('UPDATE device_sensor SET sensor_id = sensor_sub.id
            FROM (SELECT s.id AS id, s.sensor_id AS s_sensor_id
                FROM sensor s
                    LEFT JOIN device_sensor ds ON s.sensor_id = ds.sensor_id
            ) AS sensor_sub
            WHERE device_sensor.sensor_id = sensor_sub.s_sensor_id'
        );
    }
}
