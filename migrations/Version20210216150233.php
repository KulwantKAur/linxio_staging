<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216150233 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8C');

        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4ACF214C8C');
        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4AC5B1F2B51');
        $this->addSql('DROP SEQUENCE notification_alert_subtype_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_alert_setting_id_seq CASCADE');
        $this->addSql('DROP TABLE notification_alert_subtype');
        $this->addSql('DROP TABLE notification_alert_setting');
        $this->addSql('CREATE SEQUENCE notification_alert_subtype_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_alert_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_alert_subtype (id INT NOT NULL, name VARCHAR(255) NOT NULL, sort INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D8A48A95E237E06 ON notification_alert_subtype (name)');
        $this->addSql('CREATE TABLE notification_alert_setting (id INT NOT NULL, event_id INT NOT NULL, alert_type_id INT NOT NULL, alert_subtype_id INT NOT NULL, team VARCHAR(50) NOT NULL, PRIMARY KEY(id),  sort INT DEFAULT 0 NOT NULL)');
        $this->addSql('CREATE INDEX IDX_837DC4AC71F7E88B ON notification_alert_setting (event_id)');
        $this->addSql('CREATE INDEX IDX_837DC4AC5B1F2B51 ON notification_alert_setting (alert_type_id)');
        $this->addSql('CREATE INDEX IDX_837DC4ACF214C8C ON notification_alert_setting (alert_subtype_id)');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4AC71F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4AC5B1F2B51 FOREIGN KEY (alert_type_id) REFERENCES notification_alert_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4ACF214C8C FOREIGN KEY (alert_subtype_id) REFERENCES notification_alert_subtype (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4ACF214C8C');
        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4AC5B1F2B51');
        $this->addSql('DROP SEQUENCE notification_alert_subtype_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_alert_setting_id_seq CASCADE');
        $this->addSql('DROP TABLE notification_alert_subtype');
        $this->addSql('DROP TABLE notification_alert_setting');
        $this->addSql('CREATE SEQUENCE notification_alert_subtype_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_alert_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_alert_subtype (id INT NOT NULL, name VARCHAR(255) NOT NULL, sort INT DEFAULT 10 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D8A48A95E237E06 ON notification_alert_subtype (name)');
        $this->addSql('CREATE TABLE notification_alert_setting (id INT NOT NULL, event_id INT NOT NULL, alert_type_id INT NOT NULL, alert_subtype_id INT NOT NULL, team VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_837DC4AC71F7E88B ON notification_alert_setting (event_id)');
        $this->addSql('CREATE INDEX IDX_837DC4AC5B1F2B51 ON notification_alert_setting (alert_type_id)');
        $this->addSql('CREATE INDEX IDX_837DC4ACF214C8C ON notification_alert_setting (alert_subtype_id)');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4AC71F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4AC5B1F2B51 FOREIGN KEY (alert_type_id) REFERENCES notification_alert_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_837DC4ACF214C8C FOREIGN KEY (alert_subtype_id) REFERENCES notification_alert_subtype (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8C ON notification_alert_setting (team, event_id, alert_type_id, alert_subtype_id, sort)');
    }
}
