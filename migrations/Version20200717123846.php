<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200717123846 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX tracker_auth_created_at_idx ON tracker_auth (created_at DESC)');
        $this->addSql('CREATE INDEX tracker_history_created_at_idx ON tracker_history (created_at DESC)');
        $this->addSql('CREATE INDEX tracker_history_ts_idx ON tracker_history (ts DESC)');
        $this->addSql('CREATE INDEX route_started_at_idx ON route (started_at DESC)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX route_started_at_idx');
        $this->addSql('DROP INDEX tracker_history_created_at_idx');
        $this->addSql('DROP INDEX tracker_history_ts_idx');
        $this->addSql('DROP INDEX tracker_auth_created_at_idx');
    }
}
