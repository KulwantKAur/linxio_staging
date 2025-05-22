<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190513113403 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE vehicle_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle (id INT NOT NULL, client_id BIGINT DEFAULT NULL, depot INT DEFAULT NULL, groupId INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, model INT DEFAULT NULL, available BOOLEAN DEFAULT NULL, regNo VARCHAR(255) DEFAULT NULL, defaultLabel VARCHAR(255) DEFAULT NULL, vin VARCHAR(255) DEFAULT NULL, regDate TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, regCertNo VARCHAR(255) DEFAULT NULL, enginePower DOUBLE PRECISION DEFAULT NULL, engineCapacity DOUBLE PRECISION DEFAULT NULL, fuelType VARCHAR(50) DEFAULT NULL, emissionClass VARCHAR(50) DEFAULT NULL, co2Emissions DOUBLE PRECISION DEFAULT NULL, grossWeight DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B80E48619EB6921 ON vehicle (client_id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E48619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68E545317D1 ON device (vehicle_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E545317D1');
        $this->addSql('DROP SEQUENCE vehicle_id_seq CASCADE');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP INDEX IDX_92FB68E545317D1');
        $this->addSql('ALTER TABLE device DROP vehicle_id');
    }
}
