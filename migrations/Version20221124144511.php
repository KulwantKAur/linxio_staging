<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221124144511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE admin_team_info_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admin_team_info (id INT NOT NULL, team_id INT DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, legal_name VARCHAR(255) DEFAULT NULL, abn VARCHAR(255) DEFAULT NULL, legal_address VARCHAR(255) DEFAULT NULL, billing_address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F39FC01E296CD8AE ON admin_team_info (team_id)');
        $this->addSql('ALTER TABLE admin_team_info ADD CONSTRAINT FK_F39FC01E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE admin_team_info_id_seq CASCADE');
        $this->addSql('DROP TABLE admin_team_info');
    }
}
