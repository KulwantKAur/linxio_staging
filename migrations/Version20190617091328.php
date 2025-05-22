<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190617091328 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT fk_1b80e4868510d4de');
        $this->addSql('DROP SEQUENCE depot_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE vehicle_depot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle_depot (id INT NOT NULL, team_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, status INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_253B0B50296CD8AE ON vehicle_depot (team_id)');
        $this->addSql('CREATE INDEX IDX_253B0B50DE12AB56 ON vehicle_depot (created_by)');
        $this->addSql('CREATE INDEX IDX_253B0B5016FE72E1 ON vehicle_depot (updated_by)');
        $this->addSql('ALTER TABLE vehicle_depot ADD CONSTRAINT FK_253B0B50296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_depot ADD CONSTRAINT FK_253B0B50DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_depot ADD CONSTRAINT FK_253B0B5016FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE depot');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4868510D4DE FOREIGN KEY (depot_id) REFERENCES vehicle_depot (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E4868510D4DE');
        $this->addSql('DROP SEQUENCE vehicle_depot_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE depot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE depot (id INT NOT NULL, team_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_47948bbc296cd8ae ON depot (team_id)');
        $this->addSql('CREATE INDEX idx_47948bbcde12ab56 ON depot (created_by)');
        $this->addSql('CREATE INDEX idx_47948bbc16fe72e1 ON depot (updated_by)');
        $this->addSql('ALTER TABLE depot ADD CONSTRAINT fk_47948bbc296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE depot ADD CONSTRAINT fk_47948bbcde12ab56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE depot ADD CONSTRAINT fk_47948bbc16fe72e1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE vehicle_depot');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT fk_1b80e4868510d4de');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT fk_1b80e4868510d4de FOREIGN KEY (depot_id) REFERENCES depot (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
