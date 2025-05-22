<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200806095155 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX tracker_history_device_id_created_at_index ON tracker_history (device_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_device_id_ts_index ON tracker_history (device_id, ts)');
        $this->addSql('CREATE INDEX tracker_history_device_id_engine_hours_index ON tracker_history (device_id, engine_hours)');
        $this->addSql('CREATE INDEX tracker_auth_device_id_created_at_idx ON tracker_auth (device_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX tracker_history_device_id_created_at_index');
        $this->addSql('DROP INDEX tracker_history_device_id_ts_index');
        $this->addSql('DROP INDEX tracker_history_device_id_engine_hours_index');
        $this->addSql('DROP INDEX tracker_auth_device_id_created_at_idx');
    }
}
