<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201014092013 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE digital_form_step (id SERIAL NOT NULL, digital_form_id INT NOT NULL, step_order SMALLINT NOT NULL, title VARCHAR(1024) NOT NULL, description VARCHAR(8192) NOT NULL, condition JSON DEFAULT NULL, options JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3C76397F7F744077 ON digital_form_step (digital_form_id)');
        $this->addSql('CREATE TABLE digital_form_schedule (id SERIAL NOT NULL, digital_form_id INT NOT NULL, created_by BIGINT NOT NULL, active BOOLEAN NOT NULL, old_id INT DEFAULT 0 NOT NULL, weight SMALLINT NOT NULL, time_from TIME(0) WITHOUT TIME ZONE NOT NULL, time_to TIME(0) WITHOUT TIME ZONE NOT NULL, days JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CBA1C1C17F744077 ON digital_form_schedule (digital_form_id)');
        $this->addSql('CREATE INDEX IDX_CBA1C1C1DE12AB56 ON digital_form_schedule (created_by)');
        $this->addSql('COMMENT ON COLUMN digital_form_schedule.days IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE digital_form_schedule_recipient (id SERIAL NOT NULL, digital_form_schedule_id INT NOT NULL, type VARCHAR(64) NOT NULL, value JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B5A404A3851C4CFD ON digital_form_schedule_recipient (digital_form_schedule_id)');
        $this->addSql('COMMENT ON COLUMN digital_form_schedule_recipient.value IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE digital_form_answer (id SERIAL NOT NULL, digital_form_id INT NOT NULL, vehicle_id INT DEFAULT NULL, user_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4897E3F7F744077 ON digital_form_answer (digital_form_id)');
        $this->addSql('CREATE INDEX IDX_4897E3F545317D1 ON digital_form_answer (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_4897E3FA76ED395 ON digital_form_answer (user_id)');
        $this->addSql('CREATE TABLE digital_forms (id SERIAL NOT NULL, created_by BIGINT NOT NULL, type VARCHAR(64) NOT NULL, active BOOLEAN NOT NULL, title VARCHAR(1024) NOT NULL, old_id INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B054E77DE12AB56 ON digital_forms (created_by)');
        $this->addSql('CREATE TABLE digital_form_answer_step (id SERIAL NOT NULL, digital_form_answer_id INT NOT NULL, digital_form_step_id INT NOT NULL, file_id BIGINT DEFAULT NULL, is_pass BOOLEAN DEFAULT NULL, value VARCHAR(8096) DEFAULT NULL, duration INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3C8593D46CAE0865 ON digital_form_answer_step (digital_form_answer_id)');
        $this->addSql('CREATE INDEX IDX_3C8593D48E0EB3D2 ON digital_form_answer_step (digital_form_step_id)');
        $this->addSql('CREATE INDEX IDX_3C8593D493CB796C ON digital_form_answer_step (file_id)');
        $this->addSql('ALTER TABLE digital_form_step ADD CONSTRAINT FK_3C76397F7F744077 FOREIGN KEY (digital_form_id) REFERENCES digital_forms (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_schedule ADD CONSTRAINT FK_CBA1C1C17F744077 FOREIGN KEY (digital_form_id) REFERENCES digital_forms (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_schedule ADD CONSTRAINT FK_CBA1C1C1DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient ADD CONSTRAINT FK_B5A404A3851C4CFD FOREIGN KEY (digital_form_schedule_id) REFERENCES digital_form_schedule (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer ADD CONSTRAINT FK_4897E3F7F744077 FOREIGN KEY (digital_form_id) REFERENCES digital_forms (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer ADD CONSTRAINT FK_4897E3F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer ADD CONSTRAINT FK_4897E3FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_forms ADD CONSTRAINT FK_1B054E77DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer_step ADD CONSTRAINT FK_3C8593D46CAE0865 FOREIGN KEY (digital_form_answer_id) REFERENCES digital_form_answer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer_step ADD CONSTRAINT FK_3C8593D48E0EB3D2 FOREIGN KEY (digital_form_step_id) REFERENCES digital_form_step (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE digital_form_answer_step ADD CONSTRAINT FK_3C8593D493CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_answer_step DROP CONSTRAINT FK_3C8593D48E0EB3D2');
        $this->addSql('ALTER TABLE digital_form_schedule_recipient DROP CONSTRAINT FK_B5A404A3851C4CFD');
        $this->addSql('ALTER TABLE digital_form_answer_step DROP CONSTRAINT FK_3C8593D46CAE0865');
        $this->addSql('ALTER TABLE digital_form_step DROP CONSTRAINT FK_3C76397F7F744077');
        $this->addSql('ALTER TABLE digital_form_schedule DROP CONSTRAINT FK_CBA1C1C17F744077');
        $this->addSql('ALTER TABLE digital_form_answer DROP CONSTRAINT FK_4897E3F7F744077');
        $this->addSql('DROP TABLE digital_form_step');
        $this->addSql('DROP TABLE digital_form_schedule');
        $this->addSql('DROP TABLE digital_form_schedule_recipient');
        $this->addSql('DROP TABLE digital_form_answer');
        $this->addSql('DROP TABLE digital_forms');
        $this->addSql('DROP TABLE digital_form_answer_step');
    }
}
