<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200108115009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD driver_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA7545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA7C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_70E50DA7545317D1 ON tracker_history (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_70E50DA7C3423909 ON tracker_history (driver_id)');

        $this->updateTrackerHistoriesVehicle();
        $this->updateTrackerHistoriesDriver();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA7545317D1');
        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA7C3423909');
        $this->addSql('DROP INDEX IDX_70E50DA7545317D1');
        $this->addSql('DROP INDEX IDX_70E50DA7C3423909');
        $this->addSql('ALTER TABLE tracker_history DROP vehicle_id');
        $this->addSql('ALTER TABLE tracker_history DROP driver_id');
    }

    private function updateTrackerHistoriesVehicle()
    {
        $this->addSql('UPDATE tracker_history SET vehicle_id = th_sub.vehicle_id
            FROM (SELECT th.id AS th_id, di.vehicle_id AS vehicle_id, di.installdate
                FROM tracker_history th
                    LEFT JOIN device_installation di ON di.device_id = th.device_id
                WHERE (th.ts <= di.uninstallDate) OR (di.installDate <= th.ts AND di.uninstallDate IS NULL)
                GROUP BY th.id, di.vehicle_id, di.installdate
                ORDER BY di.installdate
            ) AS th_sub
            WHERE tracker_history.vehicle_id IS NULL 
            AND tracker_history.id = th_sub.th_id'
        );
    }

    private function updateTrackerHistoriesDriver()
    {
        $this->addSql('UPDATE tracker_history SET driver_id = th_sub.driver_id
            FROM (SELECT th.id AS th_id, th.vehicle_id AS vehicle_id, dh.driver_id AS driver_id, dh.startdate
                FROM tracker_history th
                    LEFT JOIN driver_history dh ON dh.vehicle_id = th.vehicle_id
                WHERE (th.ts <= dh.finishdate) OR (dh.startdate <= th.ts AND dh.finishdate IS NULL)
                GROUP BY th.id, th.vehicle_id, dh.driver_id, dh.startdate
                ORDER BY dh.startdate
            ) AS th_sub
            WHERE tracker_history.driver_id IS NULL
            AND tracker_history.id = th_sub.th_id'
        );
    }
}
