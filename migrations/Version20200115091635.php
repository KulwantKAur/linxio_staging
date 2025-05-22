<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115091635 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD1AEF5E5E237E068CDE5729 ON notification_event (name, type)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8C ON notification_alert_setting (team, event_id, alert_type_id, alert_subtype_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_FD1AEF5E5E237E068CDE5729');
        $this->addSql('CREATE UNIQUE INDEX uniq_fd1aef5ee16c6b948cde5729 ON notification_event (alias, type)');
        $this->addSql('DROP INDEX UNIQ_5795D593C4E0A61F71F7E88B5B1F2B51F214C8C');
    }
}
