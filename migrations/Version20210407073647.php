<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407073647 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE tracker_history_io (id SERIAL NOT NULL, device_id INT NOT NULL, vehicle_id INT DEFAULT NULL, tracker_history_on_id INT NOT NULL, tracker_history_off_id INT DEFAULT NULL, type_id INT NOT NULL, occurred_at_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, occurred_at_off TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, value_on INT DEFAULT 1 NOT NULL, value_off INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_486A3F9794A4C7D4 ON tracker_history_io (device_id)');
        $this->addSql('CREATE INDEX IDX_486A3F97545317D1 ON tracker_history_io (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_486A3F975C585BB2 ON tracker_history_io (tracker_history_on_id)');
        $this->addSql('CREATE INDEX IDX_486A3F9795F5DBB2 ON tracker_history_io (tracker_history_off_id)');
        $this->addSql('CREATE INDEX IDX_486A3F97C54C8C93 ON tracker_history_io (type_id)');
        $this->addSql('CREATE INDEX tracker_history_io_device_id_type_id_occurred_at_on_index ON tracker_history_io (device_id, type_id, occurred_at_on)');
        $this->addSql('CREATE INDEX tracker_history_io_vehicle_id_type_id_occurred_at_on_index ON tracker_history_io (vehicle_id, type_id, occurred_at_on)');
        $this->addSql('CREATE TABLE tracker_io_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F9794A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F97545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F975C585BB2 FOREIGN KEY (tracker_history_on_id) REFERENCES tracker_history (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F9795F5DBB2 FOREIGN KEY (tracker_history_off_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F97C54C8C93 FOREIGN KEY (type_id) REFERENCES tracker_io_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history_io DROP CONSTRAINT FK_486A3F97C54C8C93');
        $this->addSql('DROP TABLE tracker_history_io');
        $this->addSql('DROP TABLE tracker_io_type');
    }
}
