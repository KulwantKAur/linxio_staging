<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913101250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE integration_scope_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE integration_scope (id INT NOT NULL, integration_id INT NOT NULL, team_id INT NOT NULL, scope_type VARCHAR(255) NOT NULL, value JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8AFF02029E82DDEA ON integration_scope (integration_id)');
        $this->addSql('CREATE INDEX IDX_8AFF0202296CD8AE ON integration_scope (team_id)');
        $this->addSql('ALTER TABLE integration_scope ADD CONSTRAINT FK_8AFF02029E82DDEA FOREIGN KEY (integration_id) REFERENCES integration (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE integration_scope ADD CONSTRAINT FK_8AFF0202296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE integration_data ADD scope INT DEFAULT NULL');
        $this->addSql('ALTER TABLE integration_data ADD CONSTRAINT FK_986DCE78AF55D3 FOREIGN KEY (scope) REFERENCES integration_scope (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_986DCE78AF55D3 ON integration_data (scope)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE integration_scope_id_seq CASCADE');
        $this->addSql('ALTER TABLE integration_data DROP CONSTRAINT FK_986DCE78AF55D3');
        $this->addSql('DROP TABLE integration_scope');
        $this->addSql('DROP INDEX UNIQ_986DCE78AF55D3');
        $this->addSql('ALTER TABLE integration_data DROP scope');
    }
}
