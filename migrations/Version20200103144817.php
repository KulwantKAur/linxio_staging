<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200103144817 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE notification_alert_subtype_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_alert_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_alert_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_alert_subtype (id INT NOT NULL, name VARCHAR(255) NOT NULL, sort INT DEFAULT 10 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D8A48A95E237E06 ON notification_alert_subtype (name)');
        $this->addSql('CREATE TABLE notification_alert_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, sort INT DEFAULT 10 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F81C208F5E237E06 ON notification_alert_type (name)');
        $this->addSql('CREATE TABLE notification_alert_setting (id INT NOT NULL, event_id INT NOT NULL, alert_type_id INT NOT NULL, alert_subtype_id INT NOT NULL, team VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4ACF214C8C');
        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_837DC4AC5B1F2B51');
        $this->addSql('DROP SEQUENCE notification_alert_subtype_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_alert_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_alert_setting_id_seq CASCADE');
        $this->addSql('DROP TABLE notification_alert_subtype');
        $this->addSql('DROP TABLE notification_alert_type');
        $this->addSql('DROP TABLE notification_alert_setting');
    }
}
