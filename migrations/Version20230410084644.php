<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410084644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fuel_station_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fuel_station (id INT NOT NULL, team_id INT DEFAULT NULL, site_id VARCHAR(255) DEFAULT NULL, station_name VARCHAR(255) NOT NULL, lng NUMERIC(11, 8) NOT NULL, lat NUMERIC(11, 8) NOT NULL, address TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30058A17296CD8AE ON fuel_station (team_id)');
        $this->addSql('ALTER TABLE fuel_station ADD CONSTRAINT FK_30058A17296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE fuel_station_id_seq CASCADE');
        $this->addSql('DROP TABLE fuel_station');
    }
}
