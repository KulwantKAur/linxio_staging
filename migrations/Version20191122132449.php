<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191122132449 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card ADD last_tracker_history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD petrol_station_coordinates JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C257192664 FOREIGN KEY (last_tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_66BC0C257192664 ON fuel_card (last_tracker_history_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C257192664');
        $this->addSql('DROP INDEX IDX_66BC0C257192664');
        $this->addSql('ALTER TABLE fuel_card DROP last_tracker_history_id');
        $this->addSql('ALTER TABLE fuel_card DROP petrol_station_coordinates');
    }
}
