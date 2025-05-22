<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209124104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'INT to BIGINT part 5';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_payload_unknown ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE area_history ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_auth_unknown ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE sms ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE setting ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE driver_history ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE fuel_card ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE fuel_card_temporary ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_card_temporary_id TYPE BIGINT');
        $this->addSql('ALTER TABLE acknowledge ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE notification_message ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE driving_behavior ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE event_log ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE acknowledge ALTER event_log_id TYPE BIGINT');
        $this->addSql('ALTER TABLE notification_message ALTER event_log_id TYPE BIGINT');
        $this->addSql('ALTER TABLE idling ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE speeding ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE entity_history ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_auth ALTER id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_auth ALTER id TYPE INT');
        $this->addSql('ALTER TABLE entity_history ALTER id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ALTER id TYPE INT');
        $this->addSql('ALTER TABLE speeding ALTER id TYPE INT');
        $this->addSql('ALTER TABLE idling ALTER id TYPE INT');
        $this->addSql('ALTER TABLE acknowledge ALTER event_log_id TYPE INT');
        $this->addSql('ALTER TABLE notification_message ALTER event_log_id TYPE INT');
        $this->addSql('ALTER TABLE event_log ALTER id TYPE INT');
        $this->addSql('ALTER TABLE driving_behavior ALTER id TYPE INT');
        $this->addSql('ALTER TABLE notification_message ALTER id TYPE INT');
        $this->addSql('ALTER TABLE acknowledge ALTER id TYPE INT');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_card_temporary_id TYPE INT');
        $this->addSql('ALTER TABLE fuel_card_temporary ALTER id TYPE INT');
        $this->addSql('ALTER TABLE fuel_card ALTER id TYPE INT');
        $this->addSql('ALTER TABLE driver_history ALTER id TYPE INT');
        $this->addSql('ALTER TABLE setting ALTER id TYPE INT');
        $this->addSql('ALTER TABLE sms ALTER id TYPE INT');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER id TYPE INT');
        $this->addSql('ALTER TABLE tracker_auth_unknown ALTER id TYPE INT');
        $this->addSql('ALTER TABLE area_history ALTER id TYPE INT');
        $this->addSql('ALTER TABLE tracker_payload_unknown ALTER id TYPE INT');
    }
}
