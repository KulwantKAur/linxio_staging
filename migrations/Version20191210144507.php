<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191210144507 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE speeding_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE idling_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE speeding (id INT NOT NULL, device_id INT DEFAULT NULL, point_start_id INT DEFAULT NULL, point_finish_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT DEFAULT NULL, avg_speed INT DEFAULT NULL, max_speed INT DEFAULT NULL, distance INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7994DDCC94A4C7D4 ON speeding (device_id)');
        $this->addSql('CREATE INDEX IDX_7994DDCCF058A3F9 ON speeding (point_start_id)');
        $this->addSql('CREATE INDEX IDX_7994DDCC886802C5 ON speeding (point_finish_id)');
        $this->addSql('CREATE INDEX IDX_7994DDCC545317D1 ON speeding (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_7994DDCCC3423909 ON speeding (driver_id)');
        $this->addSql('CREATE TABLE idling (id INT NOT NULL, device_id INT DEFAULT NULL, point_start_id INT DEFAULT NULL, point_finish_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT DEFAULT NULL, address TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ECD5247A94A4C7D4 ON idling (device_id)');
        $this->addSql('CREATE INDEX IDX_ECD5247AF058A3F9 ON idling (point_start_id)');
        $this->addSql('CREATE INDEX IDX_ECD5247A886802C5 ON idling (point_finish_id)');
        $this->addSql('CREATE INDEX IDX_ECD5247A545317D1 ON idling (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_ECD5247AC3423909 ON idling (driver_id)');
        $this->addSql('ALTER TABLE speeding ADD CONSTRAINT FK_7994DDCC94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE speeding ADD CONSTRAINT FK_7994DDCCF058A3F9 FOREIGN KEY (point_start_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE speeding ADD CONSTRAINT FK_7994DDCC886802C5 FOREIGN KEY (point_finish_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE speeding ADD CONSTRAINT FK_7994DDCC545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE speeding ADD CONSTRAINT FK_7994DDCCC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE idling ADD CONSTRAINT FK_ECD5247A94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE idling ADD CONSTRAINT FK_ECD5247AF058A3F9 FOREIGN KEY (point_start_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE idling ADD CONSTRAINT FK_ECD5247A886802C5 FOREIGN KEY (point_finish_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE idling ADD CONSTRAINT FK_ECD5247A545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE idling ADD CONSTRAINT FK_ECD5247AC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history ADD is_calculated_idling BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD is_calculated_speeding BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE speeding_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE idling_id_seq CASCADE');
        $this->addSql('DROP TABLE speeding');
        $this->addSql('DROP TABLE idling');
        $this->addSql('ALTER TABLE tracker_history DROP is_calculated_idling');
        $this->addSql('ALTER TABLE tracker_history DROP is_calculated_speeding');
    }
}
