<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210118084436 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_sensor_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_sensor_type (id INT NOT NULL, vendor_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, is_available BOOLEAN DEFAULT \'true\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D3BA92405E237E06 ON device_sensor_type (name)');
        $this->addSql('CREATE INDEX IDX_D3BA9240F603EE73 ON device_sensor_type (vendor_id)');
        $this->addSql('ALTER TABLE device_sensor_type ADD CONSTRAINT FK_D3BA9240F603EE73 FOREIGN KEY (vendor_id) REFERENCES device_vendor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT fk_6f9666ab724835d3');
        $this->addSql('DROP INDEX idx_6f9666ab724835d3');
        $this->addSql('ALTER TABLE device_sensor ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD last_tracker_history_sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor DROP tracker_history_sensor_id');
        $this->addSql('ALTER TABLE device_sensor DROP type');
        $this->addSql('ALTER TABLE device_sensor DROP last_data');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABC54C8C93 FOREIGN KEY (type_id) REFERENCES device_sensor_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABE8658744 FOREIGN KEY (last_tracker_history_sensor_id) REFERENCES tracker_history_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F9666ABC54C8C93 ON device_sensor (type_id)');
        $this->addSql('CREATE INDEX IDX_6F9666ABE8658744 ON device_sensor (last_tracker_history_sensor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666ABC54C8C93');
        $this->addSql('DROP SEQUENCE device_sensor_type_id_seq CASCADE');
        $this->addSql('DROP TABLE device_sensor_type');
        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666ABE8658744');
        $this->addSql('DROP INDEX IDX_6F9666ABC54C8C93');
        $this->addSql('DROP INDEX IDX_6F9666ABE8658744');
        $this->addSql('ALTER TABLE device_sensor ADD tracker_history_sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD type INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD last_data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor DROP type_id');
        $this->addSql('ALTER TABLE device_sensor DROP last_tracker_history_sensor_id');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT fk_6f9666ab724835d3 FOREIGN KEY (tracker_history_sensor_id) REFERENCES tracker_history_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6f9666ab724835d3 ON device_sensor (tracker_history_sensor_id)');
    }
}
