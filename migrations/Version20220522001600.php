<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220522001600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE client_xero_secret_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_xero_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client_xero_secret (id INT NOT NULL, team_id INT DEFAULT NULL, xero_client_id VARCHAR(255) NOT NULL, xero_client_secret VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE client_xero_account (id INT NOT NULL, team_id INT DEFAULT NULL, xero_tenant_id VARCHAR(255), PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4CB27ADF296CD8AE ON client_xero_secret (team_id)');
        $this->addSql('CREATE INDEX IDX_4CB27ADF297CD8AE ON client_xero_account (team_id)');
        $this->addSql('ALTER TABLE client_xero_secret ADD CONSTRAINT IDX_4CB27ADF296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_xero_account ADD CONSTRAINT IDX_4CB27ADF297CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE client_xero_secret');
        $this->addSql('DROP TABLE client_xero_account');
    }
}
