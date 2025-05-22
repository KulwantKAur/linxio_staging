<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191029122940 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE IF EXISTS fuel_mark RENAME TO fuel_mapping');
        $this->addSql('ALTER SEQUENCE fuel_mark_id_seq RENAME TO fuel_mapping_id_seq');
        $this->addSql('CREATE SEQUENCE fuel_ignore_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fuel_ignore_list (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18A1F3ED5E237E06 ON fuel_ignore_list (name)');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD status VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD CONSTRAINT FK_18A1F3EDDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_ignore_list ADD CONSTRAINT FK_18A1F3ED16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_18A1F3EDDE12AB56 ON fuel_ignore_list (created_by)');
        $this->addSql('CREATE INDEX IDX_18A1F3ED16FE72E1 ON fuel_ignore_list (updated_by)');
        $this->addSql('ALTER TABLE fuel_mapping ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_mapping ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_mapping ADD status VARCHAR(100) NOT NULL  DEFAULT \'active\' ');
        $this->addSql('ALTER TABLE fuel_mapping ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_mapping ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_mapping ADD CONSTRAINT FK_E6DA5E2CDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_mapping ADD CONSTRAINT FK_E6DA5E2C16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E6DA5E2CDE12AB56 ON fuel_mapping (created_by)');
        $this->addSql('CREATE INDEX IDX_E6DA5E2C16FE72E1 ON fuel_mapping (updated_by)');
        $this->addSql('ALTER INDEX uniq_760baa605e237e06 RENAME TO UNIQ_E6DA5E2C5E237E06');
        $this->addSql('ALTER INDEX idx_760baa606a70fe35 RENAME TO IDX_E6DA5E2C6A70FE35');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE IF EXISTS fuel_mapping RENAME TO fuel_mark');
        $this->addSql('ALTER SEQUENCE fuel_mapping_id_seq RENAME TO fuel_mark_id_seq');
        $this->addSql('DROP SEQUENCE fuel_ignore_list_id_seq CASCADE');
        $this->addSql('DROP TABLE fuel_ignore_list');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP CONSTRAINT FK_18A1F3EDDE12AB56');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP CONSTRAINT FK_18A1F3ED16FE72E1');
        $this->addSql('DROP INDEX IDX_18A1F3EDDE12AB56');
        $this->addSql('DROP INDEX IDX_18A1F3ED16FE72E1');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP created_by');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP updated_by');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP status');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP created_at');
        $this->addSql('ALTER TABLE fuel_ignore_list DROP updated_at');
        $this->addSql('ALTER TABLE fuel_mapping DROP CONSTRAINT FK_E6DA5E2CDE12AB56');
        $this->addSql('ALTER TABLE fuel_mapping DROP CONSTRAINT FK_E6DA5E2C16FE72E1');
        $this->addSql('DROP INDEX IDX_E6DA5E2CDE12AB56');
        $this->addSql('DROP INDEX IDX_E6DA5E2C16FE72E1');
        $this->addSql('ALTER TABLE fuel_mapping DROP created_by');
        $this->addSql('ALTER TABLE fuel_mapping DROP updated_by');
        $this->addSql('ALTER TABLE fuel_mapping DROP status');
        $this->addSql('ALTER TABLE fuel_mapping DROP created_at');
        $this->addSql('ALTER TABLE fuel_mapping DROP updated_at');
        $this->addSql('ALTER INDEX idx_e6da5e2c6a70fe35 RENAME TO idx_760baa606a70fe35');
        $this->addSql('ALTER INDEX uniq_e6da5e2c5e237e06 RENAME TO uniq_760baa605e237e06');
    }
}
