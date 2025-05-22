<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190708085434 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE document_record_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE document_record (id INT NOT NULL, document_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, status VARCHAR(255) NOT NULL, issue_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, exp_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, notify_before INT DEFAULT NULL, cost DOUBLE PRECISION DEFAULT NULL, note TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_25562EF7C33F7837 ON document_record (document_id)');
        $this->addSql('CREATE INDEX IDX_25562EF7DE12AB56 ON document_record (created_by)');
        $this->addSql('CREATE INDEX IDX_25562EF716FE72E1 ON document_record (updated_by)');
        $this->addSql('CREATE TABLE document_record_file (document_record_id INT NOT NULL, file_id INT NOT NULL, PRIMARY KEY(document_record_id, file_id))');
        $this->addSql('CREATE INDEX IDX_EB8190AE5FC55DAD ON document_record_file (document_record_id)');
        $this->addSql('CREATE INDEX IDX_EB8190AE93CB796C ON document_record_file (file_id)');
        $this->addSql('ALTER TABLE document_record ADD CONSTRAINT FK_25562EF7C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_record ADD CONSTRAINT FK_25562EF7DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_record ADD CONSTRAINT FK_25562EF716FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_record_file ADD CONSTRAINT FK_EB8190AE5FC55DAD FOREIGN KEY (document_record_id) REFERENCES document_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_record_file ADD CONSTRAINT FK_EB8190AE93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a7693cb796c');
        $this->addSql('DROP INDEX uniq_d8698a7693cb796c');
        $this->addSql('ALTER TABLE document DROP file_id');
        $this->addSql('ALTER TABLE document DROP status');
        $this->addSql('ALTER TABLE document DROP issue_date');
        $this->addSql('ALTER TABLE document DROP exp_date');
        $this->addSql('ALTER TABLE document DROP notify_before');
        $this->addSql('ALTER TABLE document DROP cost');
        $this->addSql('ALTER TABLE document DROP note');
        $this->addSql('ALTER TABLE file ADD display_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document_record_file DROP CONSTRAINT FK_EB8190AE5FC55DAD');
        $this->addSql('DROP SEQUENCE document_record_id_seq CASCADE');
        $this->addSql('DROP TABLE document_record');
        $this->addSql('DROP TABLE document_record_file');
        $this->addSql('ALTER TABLE file DROP display_name');
        $this->addSql('ALTER TABLE document ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document ADD issue_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE document ADD exp_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD notify_before INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD cost DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE document ADD note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a7693cb796c FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_d8698a7693cb796c ON document (file_id)');
    }
}
