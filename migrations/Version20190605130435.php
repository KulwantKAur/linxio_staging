<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190605130435 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE notification_event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_event_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_recipients_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_scopes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_transports_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_scope_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_template_set_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_transport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_event (id INT NOT NULL, name VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, entity VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD1AEF5EE16C6B948CDE5729 ON notification_event (alias, type)');
        $this->addSql('CREATE TABLE notification_events2scopes_types (event_id INT NOT NULL, scope_type_id INT NOT NULL, PRIMARY KEY(event_id, scope_type_id))');
        $this->addSql('CREATE INDEX IDX_8C7DB3D971F7E88B ON notification_events2scopes_types (event_id)');
        $this->addSql('CREATE INDEX IDX_8C7DB3D9A031793A ON notification_events2scopes_types (scope_type_id)');
        $this->addSql('CREATE TABLE notification_event_template (id INT NOT NULL, set_id INT NOT NULL, event_id INT NOT NULL, template_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_33623E8D10FB0D18 ON notification_event_template (set_id)');
        $this->addSql('CREATE INDEX IDX_33623E8D71F7E88B ON notification_event_template (event_id)');
        $this->addSql('CREATE INDEX IDX_33623E8D5DA0FB8 ON notification_event_template (template_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33623E8D10FB0D1871F7E88B5DA0FB8 ON notification_event_template (set_id, event_id, template_id)');
        $this->addSql('CREATE TABLE notification_message (id INT NOT NULL, transport_type VARCHAR(255) NOT NULL, recipient VARCHAR(255) NOT NULL, body JSON NOT NULL, status VARCHAR(255) NOT NULL, sending_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN notification_message.body IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, event_id INT NOT NULL, team_id INT DEFAULT NULL, importance VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF5476CA71F7E88B ON notification (event_id)');
        $this->addSql('CREATE TABLE notification_recipients (id INT NOT NULL, notification_id INT NOT NULL, type VARCHAR(255) NOT NULL, value JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF1497E6EF1A9D84 ON notification_recipients (notification_id)');
        $this->addSql('COMMENT ON COLUMN notification_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE notification_scopes (id INT NOT NULL, type_id INT NOT NULL, notification_id INT NOT NULL, value JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0731D6DC54C8C93 ON notification_scopes (type_id)');
        $this->addSql('CREATE INDEX IDX_C0731D6DEF1A9D84 ON notification_scopes (notification_id)');
        $this->addSql('COMMENT ON COLUMN notification_scopes.value IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE notification_transports (id INT NOT NULL, notification_id INT NOT NULL, transport_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3CCCCCC7EF1A9D84 ON notification_transports (notification_id)');
        $this->addSql('CREATE INDEX IDX_3CCCCCC79909C13F ON notification_transports (transport_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3CCCCCC7EF1A9D849909C13F ON notification_transports (notification_id, transport_id)');
        $this->addSql('CREATE TABLE notification_scope_type (id INT NOT NULL, alias VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CA2B6254E16C6B94 ON notification_scope_type (alias)');
        $this->addSql('CREATE TABLE notification_template (id INT NOT NULL, transport_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, body JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C27027269909C13F ON notification_template (transport_id)');
        $this->addSql('COMMENT ON COLUMN notification_template.body IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE notification_template_set (id INT NOT NULL, name VARCHAR(255) NOT NULL, team_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE notification_transport (id INT NOT NULL, name VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0426671E16C6B94 ON notification_transport (alias)');
        $this->addSql('ALTER TABLE notification_events2scopes_types ADD CONSTRAINT FK_8C7DB3D971F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_events2scopes_types ADD CONSTRAINT FK_8C7DB3D9A031793A FOREIGN KEY (scope_type_id) REFERENCES notification_scope_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_event_template ADD CONSTRAINT FK_33623E8D10FB0D18 FOREIGN KEY (set_id) REFERENCES notification_template_set (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_event_template ADD CONSTRAINT FK_33623E8D71F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_event_template ADD CONSTRAINT FK_33623E8D5DA0FB8 FOREIGN KEY (template_id) REFERENCES notification_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA71F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_recipients ADD CONSTRAINT FK_EF1497E6EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_scopes ADD CONSTRAINT FK_C0731D6DC54C8C93 FOREIGN KEY (type_id) REFERENCES notification_scope_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_scopes ADD CONSTRAINT FK_C0731D6DEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_transports ADD CONSTRAINT FK_3CCCCCC7EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_transports ADD CONSTRAINT FK_3CCCCCC79909C13F FOREIGN KEY (transport_id) REFERENCES notification_transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_template ADD CONSTRAINT FK_C27027269909C13F FOREIGN KEY (transport_id) REFERENCES notification_transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_events2scopes_types DROP CONSTRAINT FK_8C7DB3D971F7E88B');
        $this->addSql('ALTER TABLE notification_event_template DROP CONSTRAINT FK_33623E8D71F7E88B');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA71F7E88B');
        $this->addSql('ALTER TABLE notification_recipients DROP CONSTRAINT FK_EF1497E6EF1A9D84');
        $this->addSql('ALTER TABLE notification_scopes DROP CONSTRAINT FK_C0731D6DEF1A9D84');
        $this->addSql('ALTER TABLE notification_transports DROP CONSTRAINT FK_3CCCCCC7EF1A9D84');
        $this->addSql('ALTER TABLE notification_events2scopes_types DROP CONSTRAINT FK_8C7DB3D9A031793A');
        $this->addSql('ALTER TABLE notification_scopes DROP CONSTRAINT FK_C0731D6DC54C8C93');
        $this->addSql('ALTER TABLE notification_event_template DROP CONSTRAINT FK_33623E8D5DA0FB8');
        $this->addSql('ALTER TABLE notification_event_template DROP CONSTRAINT FK_33623E8D10FB0D18');
        $this->addSql('ALTER TABLE notification_transports DROP CONSTRAINT FK_3CCCCCC79909C13F');
        $this->addSql('ALTER TABLE notification_template DROP CONSTRAINT FK_C27027269909C13F');
        $this->addSql('DROP SEQUENCE notification_event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_event_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_message_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_recipients_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_scopes_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_transports_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_scope_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_template_set_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_transport_id_seq CASCADE');
        $this->addSql('DROP TABLE notification_event');
        $this->addSql('DROP TABLE notification_events2scopes_types');
        $this->addSql('DROP TABLE notification_event_template');
        $this->addSql('DROP TABLE notification_message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_recipients');
        $this->addSql('DROP TABLE notification_scopes');
        $this->addSql('DROP TABLE notification_transports');
        $this->addSql('DROP TABLE notification_scope_type');
        $this->addSql('DROP TABLE notification_template');
        $this->addSql('DROP TABLE notification_template_set');
        $this->addSql('DROP TABLE notification_transport');
    }
}
