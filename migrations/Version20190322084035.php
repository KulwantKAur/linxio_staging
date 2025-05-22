<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190322084035 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE client_note_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE role_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client_note (id INT NOT NULL, client_id BIGINT DEFAULT NULL, created_by BIGINT DEFAULT NULL, note TEXT NOT NULL, note_type VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1E21397619EB6921 ON client_note (client_id)');
        $this->addSql('CREATE INDEX IDX_1E213976DE12AB56 ON client_note (created_by)');
        $this->addSql('CREATE TABLE role_permission (id INT NOT NULL, role VARCHAR(255) NOT NULL, team VARCHAR(50) NOT NULL, permission VARCHAR(255) NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE client_note ADD CONSTRAINT FK_1E21397619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_note ADD CONSTRAINT FK_1E213976DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E919EB6921');
        $this->addSql('ALTER TABLE users ADD role VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users DROP role_id');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E9DE12AB56 ON users (created_by)');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c7440455a76ed395');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455DE12AB56');
        $this->addSql('DROP INDEX idx_c7440455570c7bf5');
        $this->addSql('DROP INDEX idx_c7440455a76ed395');
        $this->addSql('ALTER TABLE client DROP user_id');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455570C7BF5 ON client (key_contact_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE client_note_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE role_permission_id_seq CASCADE');
        $this->addSql('DROP TABLE client_note');
        $this->addSql('DROP TABLE role_permission');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9DE12AB56');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e919eb6921');
        $this->addSql('DROP INDEX IDX_1483A5E9DE12AB56');
        $this->addSql('ALTER TABLE users ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE users DROP role');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_1483a5e919eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c7440455de12ab56');
        $this->addSql('DROP INDEX UNIQ_C7440455570C7BF5');
        $this->addSql('ALTER TABLE client ADD user_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c7440455a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c7440455de12ab56 FOREIGN KEY (created_by) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c7440455570c7bf5 ON client (key_contact_id)');
        $this->addSql('CREATE INDEX idx_c7440455a76ed395 ON client (user_id)');
    }
}
