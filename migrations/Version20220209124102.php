<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209124102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'INT to BIGINT part 3';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_payload ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER tracker_payload_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ALTER tracker_payload_id TYPE BIGINT');
        $this->addSql('ALTER TABLE traccar_event_history ALTER payload_id TYPE BIGINT');
        $this->addSql('ALTER TABLE driving_behavior ALTER tracker_payload_id TYPE BIGINT');
        $this->addSql('ALTER TABLE route_temp ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE route ALTER id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE route ALTER id TYPE INT');
        $this->addSql('ALTER TABLE route_temp ALTER id TYPE INT');
        $this->addSql('ALTER TABLE driving_behavior ALTER tracker_payload_id TYPE INT');
        $this->addSql('ALTER TABLE traccar_event_history ALTER payload_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ALTER tracker_payload_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER tracker_payload_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_payload ALTER id TYPE INT');
    }
}
