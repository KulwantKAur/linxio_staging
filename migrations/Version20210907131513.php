<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210907131513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE integration_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE integration_data (id INT NOT NULL, integration_id INT DEFAULT NULL, team_id INT DEFAULT NULL, data JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_986DCE789E82DDEA ON integration_data (integration_id)');
        $this->addSql('CREATE INDEX IDX_986DCE78296CD8AE ON integration_data (team_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_986DCE789E82DDEA296CD8AE ON integration_data (integration_id, team_id)');
        $this->addSql('ALTER TABLE integration_data ADD CONSTRAINT FK_986DCE789E82DDEA FOREIGN KEY (integration_id) REFERENCES integration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE integration_data ADD CONSTRAINT FK_986DCE78296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE integration_data_id_seq CASCADE');
        $this->addSql('DROP TABLE integration_data');
    }
}
