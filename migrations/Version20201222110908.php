<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222110908 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE reseller_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reseller (id INT NOT NULL, key_contact_id BIGINT DEFAULT NULL, logo_id BIGINT DEFAULT NULL, team_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, currency VARCHAR(30) DEFAULT NULL, domain VARCHAR(100) DEFAULT NULL, units VARCHAR(30) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, sms_name VARCHAR(200) DEFAULT NULL, email_name VARCHAR(200) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18015899A7A91E0B ON reseller (domain)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18015899570C7BF5 ON reseller (key_contact_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18015899F98F144A ON reseller (logo_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18015899296CD8AE ON reseller (team_id)');
        $this->addSql('CREATE INDEX IDX_18015899DE12AB56 ON reseller (created_by)');
        $this->addSql('CREATE INDEX IDX_1801589916FE72E1 ON reseller (updated_by)');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899570C7BF5 FOREIGN KEY (key_contact_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899F98F144A FOREIGN KEY (logo_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_1801589916FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE reseller_id_seq CASCADE');
        $this->addSql('DROP TABLE reseller');
    }
}
