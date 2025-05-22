<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809091859 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE importance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE importance (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A99BD995E237E06 ON importance (name)');
        $this->addSql('ALTER TABLE notification_event ADD importance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_event ADD CONSTRAINT FK_FD1AEF5E4C3BBD10 FOREIGN KEY (importance_id) REFERENCES importance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FD1AEF5E4C3BBD10 ON notification_event (importance_id)');
        $this->addSql('ALTER TABLE event_log ADD triggered_by VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_log ADD triggered_details VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_log ADD event_by VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_log ADD event_details VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_event DROP CONSTRAINT FK_FD1AEF5E4C3BBD10');
        $this->addSql('DROP SEQUENCE importance_id_seq CASCADE');
        $this->addSql('DROP TABLE importance');
        $this->addSql('DROP INDEX IDX_FD1AEF5E4C3BBD10');
        $this->addSql('ALTER TABLE notification_event DROP importance_id');
        $this->addSql('ALTER TABLE event_log DROP triggered_by');
        $this->addSql('ALTER TABLE event_log DROP triggered_details');
        $this->addSql('ALTER TABLE event_log DROP event_by');
        $this->addSql('ALTER TABLE event_log DROP event_details');
    }
}
