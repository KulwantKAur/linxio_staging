<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201008115510 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE vehicle_odometer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle_odometer (id INT NOT NULL, vehicle_id INT DEFAULT NULL, device_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, tracker_history_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, odometer BIGINT NOT NULL, odometer_from_device BIGINT DEFAULT NULL, accuracy BIGINT DEFAULT NULL, is_synced_with_device BOOLEAN DEFAULT \'false\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AF834C20545317D1 ON vehicle_odometer (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_AF834C2094A4C7D4 ON vehicle_odometer (device_id)');
        $this->addSql('CREATE INDEX IDX_AF834C20C3423909 ON vehicle_odometer (driver_id)');
        $this->addSql('CREATE INDEX IDX_AF834C20DFB8E61F ON vehicle_odometer (tracker_history_id)');
        $this->addSql('CREATE INDEX IDX_AF834C20DE12AB56 ON vehicle_odometer (created_by)');
        $this->addSql('CREATE INDEX IDX_AF834C2016FE72E1 ON vehicle_odometer (updated_by)');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C20545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C2094A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C20C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C20DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C20DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_odometer ADD CONSTRAINT FK_AF834C2016FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE vehicle_odometer_id_seq CASCADE');
        $this->addSql('DROP TABLE vehicle_odometer');
    }
}
