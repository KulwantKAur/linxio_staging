<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201117142824 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("UPDATE tracker_history SET ibutton = NULL WHERE ibutton = '0'");
        $this->addSql('ALTER TABLE users ADD driver_sensor_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE vehicles_drivers (vehicle_id INT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(vehicle_id, user_id))');
        $this->addSql('CREATE INDEX IDX_298F5C59545317D1 ON vehicles_drivers (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_298F5C59A76ED395 ON vehicles_drivers (user_id)');
        $this->addSql('CREATE TABLE tracker_command (id SERIAL NOT NULL, device_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, command_request VARCHAR(255) NOT NULL, type INT DEFAULT NULL, tracker_response VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, responded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D995973894A4C7D4 ON tracker_command (device_id)');
        $this->addSql('CREATE INDEX IDX_D9959738545317D1 ON tracker_command (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_D9959738DE12AB56 ON tracker_command (created_by)');
        $this->addSql('CREATE INDEX tracker_command_device_id_created_at_index ON tracker_command (device_id, created_at)');
        $this->addSql('CREATE INDEX tracker_command_vehicle_id_created_at_index ON tracker_command (vehicle_id, created_at)');
        $this->addSql('ALTER TABLE vehicles_drivers ADD CONSTRAINT FK_298F5C59545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicles_drivers ADD CONSTRAINT FK_298F5C59A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_command ADD CONSTRAINT FK_D995973894A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_command ADD CONSTRAINT FK_D9959738545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_command ADD CONSTRAINT FK_D9959738DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE SEQUENCE tracker_payload_unknown_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_payload_unknown (id INT NOT NULL, device_id INT DEFAULT NULL, payload TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, imei VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BE858C0694A4C7D4 ON tracker_payload_unknown (device_id)');
        $this->addSql('ALTER TABLE tracker_payload_unknown ADD CONSTRAINT FK_BE858C0694A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE tracker_payload_unknown_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_payload_unknown');

        $this->addSql('DROP TABLE vehicles_drivers');
        $this->addSql('DROP TABLE tracker_command');
        $this->addSql('ALTER TABLE users DROP driver_sensor_id');
    }
}
