<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209124100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'INT to BIGINT part 1';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE driving_behavior ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE vehicle_odometer ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_io ALTER tracker_history_on_id TYPE BIGINT');
        $this->addSql('ALTER TABLE device_sensor ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER tracker_history_id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE device_sensor ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_io ALTER tracker_history_on_id TYPE INT');
        $this->addSql('ALTER TABLE vehicle_odometer ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE driving_behavior ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history ALTER id TYPE INT');
    }
}
