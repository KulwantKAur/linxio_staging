<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200708120515 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE route_temp_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE route_temp (id INT NOT NULL, device_id INT DEFAULT NULL, point_start_id INT DEFAULT NULL, point_finish_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, points_count DOUBLE PRECISION DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, distance BIGINT DEFAULT NULL, max_speed INT DEFAULT NULL, avg_speed INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A616844F94A4C7D4 ON route_temp (device_id)');
        $this->addSql('CREATE INDEX IDX_A616844FF058A3F9 ON route_temp (point_start_id)');
        $this->addSql('CREATE INDEX IDX_A616844F886802C5 ON route_temp (point_finish_id)');
        $this->addSql('CREATE INDEX IDX_A616844F545317D1 ON route_temp (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_A616844FC3423909 ON route_temp (driver_id)');
        $this->addSql('ALTER TABLE route_temp ADD CONSTRAINT FK_A616844F94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_temp ADD CONSTRAINT FK_A616844FF058A3F9 FOREIGN KEY (point_start_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_temp ADD CONSTRAINT FK_A616844F886802C5 FOREIGN KEY (point_finish_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_temp ADD CONSTRAINT FK_A616844F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_temp ADD CONSTRAINT FK_A616844FC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE route_temp_id_seq CASCADE');
        $this->addSql('DROP TABLE route_temp');
    }
}
