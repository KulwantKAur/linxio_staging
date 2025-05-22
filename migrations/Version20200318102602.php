<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318102602 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE inspection_form_data_value ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_form_data_value ADD note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_form_data_value ADD CONSTRAINT FK_2EB3252993CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2EB3252993CB796C ON inspection_form_data_value (file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE inspection_form_data_value DROP CONSTRAINT FK_2EB3252993CB796C');
        $this->addSql('DROP INDEX UNIQ_2EB3252993CB796C');
        $this->addSql('ALTER TABLE inspection_form_data_value DROP file_id');
        $this->addSql('ALTER TABLE inspection_form_data_value DROP note');
    }
}
