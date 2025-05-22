<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801115906 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE notification_event_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_event_history (id INT NOT NULL, event_id INT NOT NULL, details JSON NOT NULL, notifications_list JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F45F132D71F7E88B ON notification_event_history (event_id)');
        $this->addSql('COMMENT ON COLUMN notification_event_history.details IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification_event_history.notifications_list IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE notification_event_history ADD CONSTRAINT FK_F45F132D71F7E88B FOREIGN KEY (event_id) REFERENCES notification_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE notification_event_history_id_seq CASCADE');
        $this->addSql('DROP TABLE notification_event_history');
    }
}
