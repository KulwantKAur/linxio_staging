<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102135400 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history RENAME COLUMN engine_hours TO engine_on_time');
        $this->addSql('ALTER TABLE tracker_history_last RENAME COLUMN engine_hours TO engine_on_time');
        $this->addSql('DROP INDEX tracker_history_device_id_engine_hours_index');
        $this->addSql('CREATE INDEX tracker_history_device_id_engine_on_time_index ON tracker_history (device_id, engine_on_time)');
        $this->addSql('ALTER TABLE vehicle ADD engine_on_time INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP engine_on_time');
        $this->addSql('DROP INDEX tracker_history_device_id_engine_on_time_index');
        $this->addSql('ALTER TABLE tracker_history_last RENAME COLUMN engine_on_time TO engine_hours');
        $this->addSql('ALTER TABLE tracker_history RENAME COLUMN engine_on_time TO engine_hours');
        $this->addSql('CREATE INDEX tracker_history_device_id_engine_hours_index ON tracker_history (device_id, engine_hours)');
    }
}