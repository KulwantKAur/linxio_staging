<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114144752 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE tracker_auth_unknown_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_auth_unknown (id INT NOT NULL, vendor_id INT DEFAULT NULL, payload VARCHAR(255) NOT NULL, socket_id VARCHAR(255) DEFAULT NULL, imei VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_56977B5F603EE73 ON tracker_auth_unknown (vendor_id)');
        $this->addSql('ALTER TABLE tracker_auth_unknown ADD CONSTRAINT FK_56977B5F603EE73 FOREIGN KEY (vendor_id) REFERENCES device_vendor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE tracker_auth_unknown_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_auth_unknown');
    }
}
