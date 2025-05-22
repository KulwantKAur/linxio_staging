<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190319071629 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, created_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F3610DE12AB56 ON file (created_by)');
        $this->addSql('CREATE TABLE users (id BIGINT NOT NULL, client_id BIGINT DEFAULT NULL, picture_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, team_type VARCHAR(255) NOT NULL, role_id INT NOT NULL, position VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by BIGINT DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_by BIGINT DEFAULT NULL, last_logged_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX IDX_1483A5E919EB6921 ON users (client_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9EE45BDBF ON users (picture_id)');
        $this->addSql('CREATE TABLE client (id BIGINT NOT NULL, manager_id BIGINT DEFAULT NULL, key_contact_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, created_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, accounting_contact_id INT DEFAULT NULL, timezone VARCHAR(50) DEFAULT NULL, plan_id INT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, legal_name VARCHAR(255) DEFAULT NULL, legal_address VARCHAR(255) DEFAULT NULL, billing_address VARCHAR(255) DEFAULT NULL, Tax_nr VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_by BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7440455783E3463 ON client (manager_id)');
        $this->addSql('CREATE INDEX IDX_C7440455570C7BF5 ON client (key_contact_id)');
        $this->addSql('CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)');
        $this->addSql('CREATE INDEX IDX_C7440455DE12AB56 ON client (created_by)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9EE45BDBF FOREIGN KEY (picture_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455783E3463 FOREIGN KEY (manager_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455570C7BF5 FOREIGN KEY (key_contact_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9EE45BDBF');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610DE12AB56');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455783E3463');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455570C7BF5');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455A76ED395');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455DE12AB56');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E919EB6921');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE client');
    }
}
