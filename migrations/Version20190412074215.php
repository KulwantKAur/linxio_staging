<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190412074215 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client ALTER accounting_contact_id TYPE BIGINT');
        $this->addSql('ALTER TABLE client ALTER accounting_contact_id DROP DEFAULT');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045584F44284 FOREIGN KEY (accounting_contact_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045584F44284 ON client (accounting_contact_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C744045584F44284');
        $this->addSql('DROP INDEX UNIQ_C744045584F44284');
        $this->addSql('ALTER TABLE client ALTER accounting_contact_id TYPE INT');
        $this->addSql('ALTER TABLE client ALTER accounting_contact_id DROP DEFAULT');
    }
}
