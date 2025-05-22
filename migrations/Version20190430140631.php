<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190430140631 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device (id INT NOT NULL, client_id BIGINT DEFAULT NULL, vendor VARCHAR(255) NOT NULL, model VARCHAR(255) DEFAULT NULL, sn VARCHAR(255) DEFAULT NULL, status VARCHAR(100) DEFAULT NULL, port INT DEFAULT NULL, hw VARCHAR(255) DEFAULT NULL, sw VARCHAR(255) DEFAULT NULL, imei VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, imsi VARCHAR(255) DEFAULT NULL, devEui VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92FB68E19EB6921 ON device (client_id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE device_id_seq CASCADE');
        $this->addSql('DROP TABLE device');
    }
}
