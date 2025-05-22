<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020073939 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_step ALTER COLUMN "options" TYPE jsonb USING "options"::jsonb;');
        $this->addSql('ALTER TABLE digital_form_step ALTER COLUMN "condition" TYPE jsonb USING "condition"::jsonb;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER COLUMN "value" TYPE jsonb USING "value"::jsonb;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN "days" TYPE jsonb USING "days"::jsonb;');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient ALTER COLUMN "value" TYPE jsonb USING "value"::jsonb;');
        $this->addSql('ALTER TABLE digital_form_schedule ADD "is_default" BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN digital_form_schedule.days IS NULL');
        $this->addSql('COMMENT ON COLUMN digital_form_schedule_recipient.value IS NULL');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient ADD additional_type VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient ADD additional_value JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_form_schedule DROP active');
        $this->addSql('ALTER TABLE digital_form_schedule DROP old_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('COMMENT ON COLUMN digital_form_schedule_recipient.value IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE digital_form_schedule DROP "is_default"');
        $this->addSql('COMMENT ON COLUMN digital_form_schedule.days IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE digital_form_step ALTER COLUMN "options" TYPE json USING "options"::json;');
        $this->addSql('ALTER TABLE digital_form_step ALTER COLUMN "condition" TYPE json USING "condition"::json;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER COLUMN "value" TYPE json USING "value"::json;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN "days" TYPE json USING "days"::json;');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient ALTER COLUMN "value" TYPE json USING "value"::json;');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient DROP additional_type');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient DROP additional_value');
        $this->addSql('ALTER TABLE digital_form_schedule ADD active BOOLEAN NOT NULL DEFAULT \'false\'');
        $this->addSql('ALTER TABLE digital_form_schedule ADD old_id INT DEFAULT 0 NOT NULL');
    }
}
