<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210708142449 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE platform_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE platform_setting (id INT NOT NULL, logo_id BIGINT DEFAULT NULL, team_id INT DEFAULT NULL, favicon_id BIGINT DEFAULT NULL, client_default_theme_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, currency VARCHAR(30) DEFAULT NULL, domain VARCHAR(100) DEFAULT NULL, units VARCHAR(30) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, sms_name VARCHAR(200) DEFAULT NULL, email_name VARCHAR(200) DEFAULT NULL, product_name VARCHAR(200) DEFAULT NULL, support_msg TEXT DEFAULT NULL, support_email VARCHAR(200) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A4472CAA7A91E0B ON platform_setting (domain)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A4472CAF98F144A ON platform_setting (logo_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A4472CA296CD8AE ON platform_setting (team_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A4472CAD78119FD ON platform_setting (favicon_id)');
        $this->addSql('CREATE INDEX IDX_8A4472CA7B8FC132 ON platform_setting (client_default_theme_id)');
        $this->addSql('CREATE INDEX IDX_8A4472CADE12AB56 ON platform_setting (created_by)');
        $this->addSql('CREATE INDEX IDX_8A4472CA16FE72E1 ON platform_setting (updated_by)');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CAF98F144A FOREIGN KEY (logo_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CA296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CAD78119FD FOREIGN KEY (favicon_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CA7B8FC132 FOREIGN KEY (client_default_theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CADE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CA16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT fk_18015899f98f144a');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT fk_18015899d78119fd');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT fk_180158997b8fc132');
        $this->addSql('DROP INDEX uniq_18015899a7a91e0b');
        $this->addSql('DROP INDEX uniq_18015899d78119fd');
        $this->addSql('DROP INDEX idx_180158997b8fc132');
        $this->addSql('DROP INDEX uniq_18015899f98f144a');
        $this->addSql('ALTER TABLE reseller DROP logo_id');
        $this->addSql('ALTER TABLE reseller DROP favicon_id');
        $this->addSql('ALTER TABLE reseller DROP client_default_theme_id');
        $this->addSql('ALTER TABLE reseller DROP currency');
        $this->addSql('ALTER TABLE reseller DROP domain');
        $this->addSql('ALTER TABLE reseller DROP units');
        $this->addSql('ALTER TABLE reseller DROP phone');
        $this->addSql('ALTER TABLE reseller DROP sms_name');
        $this->addSql('ALTER TABLE reseller DROP email_name');
        $this->addSql('ALTER TABLE reseller DROP product_name');
        $this->addSql('ALTER TABLE reseller DROP support_msg');
        $this->addSql('ALTER TABLE reseller DROP support_email');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE platform_setting_id_seq CASCADE');
        $this->addSql('DROP TABLE platform_setting');
        $this->addSql('ALTER TABLE reseller ADD logo_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD favicon_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD client_default_theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD currency VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD domain VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD units VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD phone VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD sms_name VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD email_name VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD product_name VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD support_msg TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD support_email VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT fk_18015899f98f144a FOREIGN KEY (logo_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT fk_18015899d78119fd FOREIGN KEY (favicon_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT fk_180158997b8fc132 FOREIGN KEY (client_default_theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_18015899a7a91e0b ON reseller (domain)');
        $this->addSql('CREATE UNIQUE INDEX uniq_18015899d78119fd ON reseller (favicon_id)');
        $this->addSql('CREATE INDEX idx_180158997b8fc132 ON reseller (client_default_theme_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_18015899f98f144a ON reseller (logo_id)');
    }
}
