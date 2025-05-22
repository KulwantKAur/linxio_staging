<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231214103738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN acknowledge_recipients.value IS NULL');
        $this->addSql('COMMENT ON COLUMN digital_forms.emails IS NULL');
        $this->addSql('COMMENT ON COLUMN event_log.details IS NULL');
        $this->addSql('COMMENT ON COLUMN event_log.notifications_list IS NULL');
        $this->addSql('COMMENT ON COLUMN event_log.event_details IS NULL');
        $this->addSql('COMMENT ON COLUMN event_log.triggered_by_details IS NULL');
        $this->addSql('COMMENT ON COLUMN fuel_card_temporary.comments IS NULL');
        $this->addSql('COMMENT ON COLUMN notification.event_tracking_days IS NULL');
        $this->addSql('COMMENT ON COLUMN notification.additional_params IS NULL');
        $this->addSql('COMMENT ON COLUMN notification_event.additional_settings IS NULL');
        $this->addSql('COMMENT ON COLUMN notification_message.body IS NULL');
        $this->addSql('COMMENT ON COLUMN notification_recipients.value IS NULL');
        $this->addSql('COMMENT ON COLUMN notification_scopes.value IS NULL');
        $this->addSql('COMMENT ON COLUMN notification_template.body IS NULL');
        $this->addSql('COMMENT ON COLUMN scheduled_report.interval IS NULL');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.value IS NULL');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.custom IS NULL');
        $this->addSql('COMMENT ON COLUMN theme.theme IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN theme.theme IS \'(DC2Type:json_array)\'');

        $this->addSql('COMMENT ON COLUMN notification_message.body IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification_template.body IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification_event.additional_settings IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification_scopes.value IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification.event_tracking_days IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN notification.additional_params IS \'(DC2Type:json_array)\'');

        $this->addSql('COMMENT ON COLUMN event_log.triggered_by_details IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN event_log.event_details IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN event_log.details IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN event_log.notifications_list IS \'(DC2Type:json_array)\'');

        $this->addSql('COMMENT ON COLUMN fuel_card_temporary.comments IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN digital_forms.emails IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN acknowledge_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN scheduled_report."interval" IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.custom IS \'(DC2Type:json_array)\'');
    }
}
