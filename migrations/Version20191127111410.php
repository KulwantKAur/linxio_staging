<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127111410 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT fk_66bc0c257192664');
        $this->addSql('DROP INDEX idx_66bc0c257192664');
        $this->addSql('ALTER TABLE fuel_card DROP last_tracker_history_id');
        $this->addSql('ALTER TABLE fuel_card DROP petrol_station_coordinates');
        $this->addSql('ALTER TABLE fuel_card ADD vehicle_coordinates geometry(POINT, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD petrol_station_coordinates geometry(POINT, 0) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card DROP petrol_station_coordinates');
        $this->addSql('ALTER TABLE fuel_card DROP vehicle_coordinates');
        $this->addSql('ALTER TABLE fuel_card ADD petrol_station_coordinates JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD last_tracker_history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT fk_66bc0c257192664 FOREIGN KEY (last_tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_66bc0c257192664 ON fuel_card (last_tracker_history_id)');
    }
}
