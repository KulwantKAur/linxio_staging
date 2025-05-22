<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190704102903 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE tracker_payload_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_payload (id INT NOT NULL, tracker_auth_id INT DEFAULT NULL, payload TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_157317F9D7DDFC70 ON tracker_payload (tracker_auth_id)');
        $this->addSql('ALTER TABLE tracker_payload ADD CONSTRAINT FK_157317F9D7DDFC70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->insertPayloadIntoNewTable();

        $this->addSql('CREATE SEQUENCE tracker_sensor_event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tracker_sensor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_sensor_event (id INT NOT NULL, device_model_id INT DEFAULT NULL, remote_id INT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9B81A0CEF741EEC7 ON tracker_sensor_event (device_model_id)');
        $this->addSql('CREATE TABLE tracker_sensor (id INT NOT NULL, tracker_payload_id INT DEFAULT NULL, tracker_history_id INT DEFAULT NULL, event_id INT DEFAULT NULL, event_value VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B4EDEC410DB296A ON tracker_sensor (tracker_payload_id)');
        $this->addSql('CREATE INDEX IDX_8B4EDEC4DFB8E61F ON tracker_sensor (tracker_history_id)');
        $this->addSql('CREATE INDEX IDX_8B4EDEC471F7E88B ON tracker_sensor (event_id)');
        $this->addSql('ALTER TABLE tracker_sensor_event ADD CONSTRAINT FK_9B81A0CEF741EEC7 FOREIGN KEY (device_model_id) REFERENCES device_model (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT FK_8B4EDEC410DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT FK_8B4EDEC4DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT FK_8B4EDEC471F7E88B FOREIGN KEY (event_id) REFERENCES tracker_sensor_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT fk_70e50da7d7ddfc70');
        $this->addSql('DROP INDEX idx_70e50da7d7ddfc70');
        $this->addSql('ALTER TABLE tracker_history DROP sensor_data');
        $this->addSql('ALTER TABLE tracker_history DROP payload');

        $this->updateTrackerHistory();

        $this->addSql('ALTER TABLE tracker_history RENAME COLUMN tracker_auth_id TO tracker_payload_id');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA710DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_70E50DA710DB296A ON tracker_history (tracker_payload_id)');

        $this->removeUnusedPayload();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('truncate table tracker_sensor CASCADE');
        $this->addSql('truncate table tracker_history CASCADE');
        $this->addSql('truncate table tracker_payload CASCADE');
        $this->addSql('truncate table tracker_auth CASCADE');

        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA710DB296A');
        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT FK_8B4EDEC410DB296A');
        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT FK_8B4EDEC471F7E88B');
        $this->addSql('DROP SEQUENCE tracker_payload_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tracker_sensor_event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tracker_sensor_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_payload');
        $this->addSql('DROP TABLE tracker_sensor_event');
        $this->addSql('DROP TABLE tracker_sensor');
        $this->addSql('DROP INDEX IDX_70E50DA710DB296A');
        $this->addSql('ALTER TABLE tracker_history ADD sensor_data TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD payload TEXT NOT NULL');
        $this->addSql('ALTER TABLE tracker_history RENAME COLUMN tracker_payload_id TO tracker_auth_id');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT fk_70e50da7d7ddfc70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_70e50da7d7ddfc70 ON tracker_history (tracker_auth_id)');
    }

    private function insertPayloadIntoNewTable()
    {
        $this->addSql('INSERT INTO tracker_payload (id, tracker_auth_id, payload, created_at) SELECT (setval(\'tracker_payload_id_seq\',nextval(\'tracker_payload_id_seq\'))), tracker_auth_id, payload, created_at FROM tracker_history;');
    }

    private function updateTrackerHistory()
    {
        $this->addSql('UPDATE tracker_history SET tracker_auth_id = payload.tp_id FROM (SELECT tp.id AS tp_id, tp.tracker_auth_id AS tp_a_id FROM tracker_payload tp LEFT JOIN tracker_history th ON tp.tracker_auth_id = th.tracker_auth_id WHERE tp.tracker_auth_id = th.tracker_auth_id) AS payload WHERE tracker_auth_id = payload.tp_a_id');
    }

    private function removeUnusedPayload()
    {
        $this->addSql('DELETE FROM tracker_payload WHERE id NOT IN (SELECT tracker_payload_id FROM tracker_history)');
    }
}
