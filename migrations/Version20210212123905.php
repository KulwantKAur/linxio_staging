<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212123905 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_sensor_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_sensor_history (id INT NOT NULL, device_id INT NOT NULL, sensor_id INT NOT NULL, installed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uninstalled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CEE06F194A4C7D4 ON device_sensor_history (device_id)');
        $this->addSql('CREATE INDEX IDX_9CEE06F1A247991F ON device_sensor_history (sensor_id)');
        $this->addSql('CREATE INDEX device_sensor_history_device_id_sensor_id_installed_at_index ON device_sensor_history (device_id, sensor_id, installed_at)');
        $this->addSql('CREATE INDEX device_sensor_history_device_id_sensor_id_uninstalled_at_index ON device_sensor_history (device_id, sensor_id, uninstalled_at)');
        $this->addSql('ALTER TABLE device_sensor_history ADD CONSTRAINT FK_9CEE06F194A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor_history ADD CONSTRAINT FK_9CEE06F1A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE device_sensor_history_id_seq CASCADE');
        $this->addSql('DROP TABLE device_sensor_history');
    }
}
