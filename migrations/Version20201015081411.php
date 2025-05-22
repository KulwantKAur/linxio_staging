<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201015081411 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value DROP DEFAULT;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value TYPE JSON USING to_json(value)::json;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER COLUMN value DROP NOT NULL;');
        $this->addSql('UPDATE digital_form_answer_step SET value = NULL WHERE json_typeof(value) IS NULL;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value SET DEFAULT NULL;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value DROP DEFAULT;');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value TYPE VARCHAR(8096);');
        $this->addSql('ALTER TABLE digital_form_answer_step ALTER value SET DEFAULT NULL');
    }
}
