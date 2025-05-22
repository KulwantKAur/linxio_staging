<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190410073807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE role_permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE plan_id_seq CASCADE');
        $this->addSql('CREATE TABLE permission (id INT NOT NULL, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE role (id INT NOT NULL, name VARCHAR(255) NOT NULL, team VARCHAR(50) NOT NULL, display_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE users ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users DROP role');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');
        $this->addSql('ALTER TABLE role_permission ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE role_permission ADD permission_id INT NOT NULL');
        $this->addSql('ALTER TABLE role_permission DROP id');
        $this->addSql('ALTER TABLE role_permission DROP role');
        $this->addSql('ALTER TABLE role_permission DROP team');
        $this->addSql('ALTER TABLE role_permission DROP permission');
        $this->addSql('ALTER TABLE role_permission DROP value');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F7DF886D60322AC ON role_permission (role_id)');
        $this->addSql('CREATE INDEX IDX_6F7DF886FED90CCA ON role_permission (permission_id)');
        $this->addSql('ALTER TABLE role_permission ADD PRIMARY KEY (role_id, permission_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE role_permission DROP CONSTRAINT FK_6F7DF886FED90CCA');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE role_permission DROP CONSTRAINT FK_6F7DF886D60322AC');
        $this->addSql('CREATE SEQUENCE role_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP INDEX IDX_6F7DF886D60322AC');
        $this->addSql('DROP INDEX IDX_6F7DF886FED90CCA');
        $this->addSql('DROP INDEX role_permission_pkey');
        $this->addSql('ALTER TABLE role_permission ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE role_permission ADD role VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE role_permission ADD team VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE role_permission ADD permission VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE role_permission ADD value INT NOT NULL');
        $this->addSql('ALTER TABLE role_permission DROP role_id');
        $this->addSql('ALTER TABLE role_permission DROP permission_id');
        $this->addSql('ALTER TABLE role_permission ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX IDX_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users ADD role VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users DROP role_id');
    }
}
