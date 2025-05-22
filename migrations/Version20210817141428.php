<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817141428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_alert_setting ADD plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_alert_setting ADD CONSTRAINT FK_5795D593E899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5795D593E899029B ON notification_alert_setting (plan_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8CE899029B ON notification_alert_setting (team, event_id, alert_type_id, alert_subtype_id, plan_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_alert_setting DROP CONSTRAINT FK_5795D593E899029B');
        $this->addSql('DROP INDEX IDX_5795D593E899029B');
        $this->addSql('ALTER TABLE notification_alert_setting DROP plan_id');
        $this->addSql('DROP INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8CE899029B');
    }
}
