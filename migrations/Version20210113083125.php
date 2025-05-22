<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210113083125 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX device_sensor_device_id_updated_at_index ON device_sensor (device_id, updated_at)');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD device_sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E99A78D8E FOREIGN KEY (device_sensor_id) REFERENCES device_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F02CE63E99A78D8E ON tracker_history_sensor (device_sensor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT FK_F02CE63E99A78D8E');
        $this->addSql('DROP INDEX IDX_F02CE63E99A78D8E');
        $this->addSql('ALTER TABLE tracker_history_sensor DROP device_sensor_id');
        $this->addSql('DROP INDEX device_sensor_device_id_updated_at_index');
    }
}
