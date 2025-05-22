<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191029133043 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE driving_behavior_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE driving_behavior (id INT NOT NULL DEFAULT nextval(\'driving_behavior_id_seq\'), tracker_history_id INT DEFAULT NULL, device_id INT DEFAULT NULL, vehicle_id INT NOT NULL, driver_id BIGINT DEFAULT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, speed DOUBLE PRECISION DEFAULT NULL, odometer DOUBLE PRECISION DEFAULT NULL, harsh_acceleration SMALLINT DEFAULT NULL, harsh_braking SMALLINT DEFAULT NULL, harsh_cornering SMALLINT DEFAULT NULL, ignition SMALLINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E08B3788DFB8E61F ON driving_behavior (tracker_history_id)');
        $this->addSql('CREATE INDEX IDX_E08B378894A4C7D4 ON driving_behavior (device_id)');
        $this->addSql('CREATE INDEX IDX_E08B3788545317D1 ON driving_behavior (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_E08B3788C3423909 ON driving_behavior (driver_id)');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B3788DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B378894A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B3788545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B3788C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE driving_behavior');
        $this->addSql('DROP SEQUENCE driving_behavior_id_seq CASCADE');
    }
}
