<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201218102909 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_sensor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_sensor (id INT NOT NULL, device_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, tracker_history_id INT DEFAULT NULL, type INT DEFAULT NULL, sensor_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F9666ABA247991F ON device_sensor (sensor_id)');
        $this->addSql('CREATE INDEX IDX_6F9666AB94A4C7D4 ON device_sensor (device_id)');
        $this->addSql('CREATE INDEX IDX_6F9666ABDE12AB56 ON device_sensor (created_by)');
        $this->addSql('CREATE INDEX IDX_6F9666AB16FE72E1 ON device_sensor (updated_by)');
        $this->addSql('CREATE INDEX IDX_6F9666ABDFB8E61F ON device_sensor (tracker_history_id)');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666AB94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666AB16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABDFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE device_sensor_id_seq CASCADE');
        $this->addSql('DROP TABLE device_sensor');
    }
}
