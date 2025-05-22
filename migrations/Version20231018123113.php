<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231018123113 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX dce_vehicle_id_started_at_finished_at_idx ON device_camera_event (vehicle_id, started_at, finished_at)');

        $this->addSql('ALTER TABLE device_camera_event_file ADD tracker_history_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_camera_event_file ADD CONSTRAINT FK_8A3E7BAADFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8A3E7BAADFB8E61F ON device_camera_event_file (tracker_history_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX IDX_8A3E7BAADFB8E61F');
        $this->addSql('ALTER TABLE device_camera_event_file DROP CONSTRAINT FK_8A3E7BAADFB8E61F');
        $this->addSql('ALTER TABLE device_camera_event_file DROP tracker_history_id');

        $this->addSql('DROP INDEX dce_vehicle_id_started_at_finished_at_idx');
    }
}
