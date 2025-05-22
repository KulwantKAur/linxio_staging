<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190423105120 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE setting (id INT NOT NULL, client_id BIGINT DEFAULT NULL, role_id INT DEFAULT NULL, team VARCHAR(50) DEFAULT NULL, name VARCHAR(100) NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F74B89819EB6921 ON setting (client_id)');
        $this->addSql('CREATE INDEX IDX_9F74B898D60322AC ON setting (role_id)');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B89819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP is_2fa_enabled');
        $this->addSql('DROP INDEX uniq_a79c98c1a76ed395');
        $this->addSql('CREATE INDEX IDX_A79C98C1A76ED395 ON otp (user_id)');
        $this->addSql('ALTER INDEX idx_3107a4eaa76ed395 RENAME TO IDX_C89E5843A76ED395');
        $this->addSql('ALTER INDEX idx_3107a4ea19eb6921 RENAME TO IDX_C89E584319EB6921');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE setting_id_seq CASCADE');
        $this->addSql('DROP TABLE setting');
        $this->addSql('ALTER TABLE users ADD is_2fa_enabled BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('DROP INDEX IDX_A79C98C1A76ED395');
        $this->addSql('CREATE UNIQUE INDEX uniq_a79c98c1a76ed395 ON otp (user_id)');
        $this->addSql('ALTER INDEX idx_c89e5843a76ed395 RENAME TO idx_3107a4eaa76ed395');
        $this->addSql('ALTER INDEX idx_c89e584319eb6921 RENAME TO idx_3107a4ea19eb6921');
    }
}
