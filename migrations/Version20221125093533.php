<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221125093533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE billing_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE billing_setting (id INT NOT NULL, team_id INT DEFAULT NULL, account_name VARCHAR(255) DEFAULT NULL, bsb VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, swift_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F45A334F296CD8AE ON billing_setting (team_id)');
        $this->addSql('ALTER TABLE billing_setting ADD CONSTRAINT FK_F45A334F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP SEQUENCE billing_setting_id_seq CASCADE');
        $this->addSql('DROP TABLE billing_setting');
    }
}
