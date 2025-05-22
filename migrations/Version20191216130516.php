<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216130516 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_message ADD event_log_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_message ADD notification_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_message ADD update_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_message ADD update_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_message ADD CONSTRAINT FK_A3A3BAC8D8FE2AD4 FOREIGN KEY (event_log_id) REFERENCES event_log (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_message ADD CONSTRAINT FK_A3A3BAC8EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_message ADD CONSTRAINT FK_A3A3BAC855645FA3 FOREIGN KEY (update_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_message ADD is_read BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_A3A3BAC8D8FE2AD4 ON notification_message (event_log_id)');
        $this->addSql('CREATE INDEX IDX_A3A3BAC8EF1A9D84 ON notification_message (notification_id)');
        $this->addSql('CREATE INDEX IDX_A3A3BAC855645FA3 ON notification_message (update_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_message DROP CONSTRAINT FK_A3A3BAC8D8FE2AD4');
        $this->addSql('ALTER TABLE notification_message DROP CONSTRAINT FK_A3A3BAC8EF1A9D84');
        $this->addSql('ALTER TABLE notification_message DROP CONSTRAINT FK_A3A3BAC855645FA3');
        $this->addSql('DROP INDEX IDX_A3A3BAC8D8FE2AD4');
        $this->addSql('DROP INDEX IDX_A3A3BAC8EF1A9D84');
        $this->addSql('DROP INDEX IDX_A3A3BAC855645FA3');
        $this->addSql('ALTER TABLE notification_message DROP is_read');
        $this->addSql('ALTER TABLE notification_message DROP event_log_id');
        $this->addSql('ALTER TABLE notification_message DROP notification_id');
        $this->addSql('ALTER TABLE notification_message DROP update_by');
        $this->addSql('ALTER TABLE notification_message DROP update_at');
    }
}
