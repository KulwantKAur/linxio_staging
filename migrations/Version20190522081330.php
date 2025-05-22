<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190522081330 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_installation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_installation (id INT NOT NULL, device_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, installDate TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uninstallDate TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6ECF8D6094A4C7D4 ON device_installation (device_id)');
        $this->addSql('CREATE INDEX IDX_6ECF8D60545317D1 ON device_installation (vehicle_id)');
        $this->addSql('ALTER TABLE device_installation ADD CONSTRAINT FK_6ECF8D6094A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_installation ADD CONSTRAINT FK_6ECF8D60545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE device_installation_id_seq CASCADE');
        $this->addSql('DROP TABLE device_installation');
    }
}
