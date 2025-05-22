<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210602114119 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX tracker_history_temp_device_id_speed_ts_index ON tracker_history_temp (device_id, speed, ts)');
        $this->addSql('CREATE INDEX tracker_history_device_id_speed_ts_index ON tracker_history (device_id, speed, ts)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX tracker_history_device_id_speed_ts_index');
        $this->addSql('DROP INDEX tracker_history_temp_device_id_speed_ts_index');
    }
}
