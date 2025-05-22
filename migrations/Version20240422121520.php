<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422121520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE route_finish_area_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE route_start_area_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE route_finish_area (id BIGINT NOT NULL, area_id INT DEFAULT NULL, route_id BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEC5DE75BD0F409C ON route_finish_area (area_id)');
        $this->addSql('CREATE INDEX IDX_EEC5DE7534ECB4E6 ON route_finish_area (route_id)');
        $this->addSql('CREATE INDEX route_finish_area_route_id_area_id_idx ON route_finish_area (route_id, area_id)');
        $this->addSql('CREATE TABLE route_start_area (id BIGINT NOT NULL, area_id INT DEFAULT NULL, route_id BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX route_start_area_route_id_area_id_idx ON route_start_area (route_id, area_id)');
        $this->addSql('CREATE INDEX IDX_E65A9A4DBD0F409C ON route_start_area (area_id)');
        $this->addSql('CREATE INDEX IDX_E65A9A4D34ECB4E6 ON route_start_area (route_id)');
        $this->addSql('ALTER TABLE route_finish_area ADD CONSTRAINT FK_EEC5DE75BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_finish_area ADD CONSTRAINT FK_EEC5DE7534ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT FK_E65A9A4DBD0F409C FOREIGN KEY (area_id) REFERENCES area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT FK_E65A9A4D34ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE route_finish_area_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE route_start_area_id_seq CASCADE');

        $this->addSql('ALTER TABLE route_finish_area DROP CONSTRAINT FK_EEC5DE75BD0F409C');
        $this->addSql('ALTER TABLE route_finish_area DROP CONSTRAINT FK_EEC5DE7534ECB4E6');
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT FK_E65A9A4DBD0F409C');
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT FK_E65A9A4D34ECB4E6');
        $this->addSql('DROP TABLE route_finish_area');
        $this->addSql('DROP TABLE route_start_area');
    }
}
