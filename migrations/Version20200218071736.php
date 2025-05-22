<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200218071736 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE inspection_form_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inspection_form_data_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inspection_form_version_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inspection_form_file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inspection_form_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inspection_form_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE inspection_form_data (id INT NOT NULL, form_id INT NOT NULL, user_id BIGINT NOT NULL, vehicle_id INT NOT NULL, version_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C5986E2C5FF69B7D ON inspection_form_data (form_id)');
        $this->addSql('CREATE INDEX IDX_C5986E2CA76ED395 ON inspection_form_data (user_id)');
        $this->addSql('CREATE INDEX IDX_C5986E2C545317D1 ON inspection_form_data (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_C5986E2C4BBC2705 ON inspection_form_data (version_id)');
        $this->addSql('CREATE TABLE inspection_form_data_value (id INT NOT NULL, if_data_id INT NOT NULL, if_template_id INT NOT NULL, value VARCHAR(255) DEFAULT NULL, time INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2EB32529C79E20CA ON inspection_form_data_value (if_data_id)');
        $this->addSql('CREATE INDEX IDX_2EB325297D947704 ON inspection_form_data_value (if_template_id)');
        $this->addSql('CREATE TABLE inspection_form_version (id INT NOT NULL, form_id INT NOT NULL, version INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA5555D15FF69B7D ON inspection_form_version (form_id)');
        $this->addSql('CREATE TABLE inspection_form_file (id INT NOT NULL, file_id INT DEFAULT NULL, if_data_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4F4AB5F93CB796C ON inspection_form_file (file_id)');
        $this->addSql('CREATE INDEX IDX_E4F4AB5FC79E20CA ON inspection_form_file (if_data_id)');
        $this->addSql('CREATE TABLE inspection_form_template (id INT NOT NULL, version_id INT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, sort INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64BC274D4BBC2705 ON inspection_form_template (version_id)');
        $this->addSql('CREATE TABLE inspection_form (id INT NOT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_789A9E53DE12AB56 ON inspection_form (created_by)');
        $this->addSql('CREATE INDEX IDX_789A9E5316FE72E1 ON inspection_form (updated_by)');
        $this->addSql('ALTER TABLE inspection_form_data ADD CONSTRAINT FK_C5986E2C5FF69B7D FOREIGN KEY (form_id) REFERENCES inspection_form (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_data ADD CONSTRAINT FK_C5986E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_data ADD CONSTRAINT FK_C5986E2C545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_data ADD CONSTRAINT FK_C5986E2C4BBC2705 FOREIGN KEY (version_id) REFERENCES inspection_form_version (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_data_value ADD CONSTRAINT FK_2EB32529C79E20CA FOREIGN KEY (if_data_id) REFERENCES inspection_form_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_data_value ADD CONSTRAINT FK_2EB325297D947704 FOREIGN KEY (if_template_id) REFERENCES inspection_form_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_version ADD CONSTRAINT FK_DA5555D15FF69B7D FOREIGN KEY (form_id) REFERENCES inspection_form (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_file ADD CONSTRAINT FK_E4F4AB5F93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_file ADD CONSTRAINT FK_E4F4AB5FC79E20CA FOREIGN KEY (if_data_id) REFERENCES inspection_form_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_template ADD CONSTRAINT FK_64BC274D4BBC2705 FOREIGN KEY (version_id) REFERENCES inspection_form_version (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form ADD CONSTRAINT FK_789A9E53DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form ADD CONSTRAINT FK_789A9E5316FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE inspection_form_data_value DROP CONSTRAINT FK_2EB32529C79E20CA');
        $this->addSql('ALTER TABLE inspection_form_file DROP CONSTRAINT FK_E4F4AB5FC79E20CA');
        $this->addSql('ALTER TABLE inspection_form_data DROP CONSTRAINT FK_C5986E2C4BBC2705');
        $this->addSql('ALTER TABLE inspection_form_template DROP CONSTRAINT FK_64BC274D4BBC2705');
        $this->addSql('ALTER TABLE inspection_form_data_value DROP CONSTRAINT FK_2EB325297D947704');
        $this->addSql('ALTER TABLE inspection_form_data DROP CONSTRAINT FK_C5986E2C5FF69B7D');
        $this->addSql('ALTER TABLE inspection_form_version DROP CONSTRAINT FK_DA5555D15FF69B7D');
        $this->addSql('DROP SEQUENCE inspection_form_data_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inspection_form_data_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inspection_form_version_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inspection_form_file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inspection_form_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inspection_form_id_seq CASCADE');
        $this->addSql('DROP TABLE inspection_form_data');
        $this->addSql('DROP TABLE inspection_form_data_value');
        $this->addSql('DROP TABLE inspection_form_version');
        $this->addSql('DROP TABLE inspection_form_file');
        $this->addSql('DROP TABLE inspection_form_template');
        $this->addSql('DROP TABLE inspection_form');
    }
}
