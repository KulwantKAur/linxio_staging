<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230407101723 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE SEQUENCE sso_integration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sso_integration_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sso_integration (id INT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE sso_integration_data (id INT NOT NULL, integration_id INT DEFAULT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, idp_entity_id TEXT NOT NULL, idp_sso_url TEXT NOT NULL, idp_slo_url TEXT DEFAULT NULL, options JSONB DEFAULT NULL, settings JSONB DEFAULT NULL, status VARCHAR(255) DEFAULT \'enabled\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5DD18CA97C572786 ON sso_integration_data (idp_entity_id)');
        $this->addSql('CREATE INDEX IDX_5DD18CA99E82DDEA ON sso_integration_data (integration_id)');
        $this->addSql('CREATE INDEX IDX_5DD18CA9296CD8AE ON sso_integration_data (team_id)');
        $this->addSql('ALTER TABLE sso_integration_data ADD CONSTRAINT FK_5DD18CA99E82DDEA FOREIGN KEY (integration_id) REFERENCES sso_integration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sso_integration_data ADD CONSTRAINT FK_5DD18CA9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE SEQUENCE sso_integration_certificate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sso_integration_certificate (id INT NOT NULL, integration_data_id INT DEFAULT NULL, certificate TEXT NOT NULL, status VARCHAR(255) DEFAULT \'enabled\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8EE2CF585AD91B65 ON sso_integration_certificate (integration_data_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EE2CF585AD91B65219CDA4A ON sso_integration_certificate (integration_data_id, certificate)');
        $this->addSql('ALTER TABLE sso_integration_certificate ADD CONSTRAINT FK_8EE2CF585AD91B65 FOREIGN KEY (integration_data_id) REFERENCES sso_integration_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE users ADD sso_integration_data_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60A9687 FOREIGN KEY (sso_integration_data_id) REFERENCES sso_integration_data (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60A9687 ON users (sso_integration_data_id)');

        $this->addSql('CREATE INDEX client_tax_nr_index ON client (tax_nr)');
        $this->addSql('CREATE INDEX reseller_tax_nr_index ON reseller (tax_nr)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX reseller_tax_nr_index');
        $this->addSql('DROP INDEX client_tax_nr_index');

        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9D60A9687');
        $this->addSql('DROP INDEX IDX_1483A5E9D60A9687');
        $this->addSql('ALTER TABLE users DROP sso_integration_data_id');

        $this->addSql('DROP SEQUENCE sso_integration_certificate_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sso_integration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sso_integration_data_id_seq CASCADE');
        $this->addSql('DROP TABLE sso_integration_certificate');
        $this->addSql('DROP TABLE sso_integration_data');
        $this->addSql('DROP TABLE sso_integration');
    }
}
