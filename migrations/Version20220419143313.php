<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220419143313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history ADD team_id INT DEFAULT NULL');
        //TODO Make migration with stopping trackers
//        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA7296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        //        $this->addSql('CREATE INDEX IDX_70E50DA7296CD8AE ON tracker_history (team_id)');
        $this->addSql('ALTER TABLE tracker_history_last ADD team_id INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295D296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_A785295D296CD8AE ON tracker_history_last (team_id)');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD team_id INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT FK_F02CE63E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_F02CE63E296CD8AE ON tracker_history_sensor (team_id)');
        $this->addSql('ALTER TABLE tracker_history_temp ADD team_id INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE tracker_history_temp ADD CONSTRAINT FK_E60D0537296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_E60D0537296CD8AE ON tracker_history_temp (team_id)');
        $this->addSql('ALTER TABLE tracker_history_io ADD team_id INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE tracker_history_io ADD CONSTRAINT FK_486A3F97296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_486A3F97296CD8AE ON tracker_history_io (team_id)');
        $this->addSql('ALTER TABLE tracker_history_io_last ADD team_id INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE tracker_history_io_last ADD CONSTRAINT FK_A17041B7296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_A17041B7296CD8AE ON tracker_history_io_last (team_id)');
    }

    public function down(Schema $schema): void
    {
//        $this->addSql('ALTER TABLE tracker_history_io DROP CONSTRAINT FK_486A3F97296CD8AE');
//        $this->addSql('DROP INDEX IDX_486A3F97296CD8AE');
        $this->addSql('ALTER TABLE tracker_history_io DROP team_id');
//        $this->addSql('ALTER TABLE tracker_history_io_last DROP CONSTRAINT FK_A17041B7296CD8AE');
//        $this->addSql('DROP INDEX IDX_A17041B7296CD8AE');
        $this->addSql('ALTER TABLE tracker_history_io_last DROP team_id');
//        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT FK_F02CE63E296CD8AE');
//        $this->addSql('DROP INDEX IDX_F02CE63E296CD8AE');
        $this->addSql('ALTER TABLE tracker_history_sensor DROP team_id');
//        $this->addSql('ALTER TABLE tracker_history_temp DROP CONSTRAINT FK_E60D0537296CD8AE');
//        $this->addSql('DROP INDEX IDX_E60D0537296CD8AE');
        $this->addSql('ALTER TABLE tracker_history_temp DROP team_id');
//        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA7296CD8AE');
//        $this->addSql('DROP INDEX IDX_70E50DA7296CD8AE');
        $this->addSql('ALTER TABLE tracker_history DROP team_id');
//        $this->addSql('ALTER TABLE tracker_history_last DROP CONSTRAINT FK_A785295D296CD8AE');
//        $this->addSql('DROP INDEX IDX_A785295D296CD8AE');
        $this->addSql('ALTER TABLE tracker_history_last DROP team_id');
    }
}
