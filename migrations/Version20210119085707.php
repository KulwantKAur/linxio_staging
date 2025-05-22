<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210119085707 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history_sensor ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F02CE63E545317D1 ON tracker_history_sensor (vehicle_id)');
        $this->addSql('CREATE INDEX tracker_history_sensor_vehicle_id_created_at_index ON tracker_history_sensor (vehicle_id, created_at)');
        $this->addSql('CREATE INDEX tracker_history_sensor_vehicle_id_occurred_at_index ON tracker_history_sensor (vehicle_id, occurred_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT FK_F02CE63E545317D1');
        $this->addSql('DROP INDEX IDX_F02CE63E545317D1');
        $this->addSql('DROP INDEX tracker_history_sensor_vehicle_id_created_at_index');
        $this->addSql('DROP INDEX tracker_history_sensor_vehicle_id_occurred_at_index');
        $this->addSql('ALTER TABLE tracker_history_sensor DROP vehicle_id');
    }
}
