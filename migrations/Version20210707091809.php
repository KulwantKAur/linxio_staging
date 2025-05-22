<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210707091809 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE asset ADD last_tracker_history_sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5CE8658744 FOREIGN KEY (last_tracker_history_sensor_id) REFERENCES tracker_history_sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2AF5A5CE8658744 ON asset (last_tracker_history_sensor_id)');

        $this->updateLastTrackerHistoryForAssets();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5CE8658744');
        $this->addSql('DROP INDEX IDX_2AF5A5CE8658744');
        $this->addSql('ALTER TABLE asset DROP last_tracker_history_sensor_id');
    }

    private function updateLastTrackerHistoryForAssets()
    {
        $this->addSql('UPDATE asset SET last_tracker_history_sensor_id = a_sub.last_tracker_history_sensor_id
            FROM (SELECT a2.id,
                   (SELECT ds2.last_tracker_history_sensor_id
                    FROM device_sensor ds2
                    WHERE a2.sensor_id = ds2.sensor_id
                    ORDER BY ds2.created_at DESC
                    LIMIT 1) AS last_tracker_history_sensor_id
                FROM asset a2
            ) AS a_sub            
            WHERE asset.id = a_sub.id'
        );
    }
}
