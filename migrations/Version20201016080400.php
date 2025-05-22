<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201016080400 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_answer_step ADD additional_file_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_form_answer_step ADD additional_note VARCHAR(8096) DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_form_answer_step ADD CONSTRAINT FK_3C8593D49241AA98 FOREIGN KEY (additional_file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3C8593D49241AA98 ON digital_form_answer_step (additional_file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_answer_step DROP CONSTRAINT FK_3C8593D49241AA98');
        $this->addSql('DROP INDEX IDX_3C8593D49241AA98');
        $this->addSql('ALTER TABLE digital_form_answer_step DROP additional_file_id');
        $this->addSql('ALTER TABLE digital_form_answer_step DROP additional_note');
    }
}
