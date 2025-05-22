<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303125342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tracker_history_jammer (id BIGSERIAL NOT NULL, device_id INT NOT NULL, vehicle_id INT DEFAULT NULL, tracker_history_on_id BIGINT NOT NULL, tracker_history_off_id BIGINT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, team_id INT DEFAULT NULL, occurred_at_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, occurred_at_off TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DF97DAB394A4C7D4 ON tracker_history_jammer (device_id)');
        $this->addSql('CREATE INDEX IDX_DF97DAB3545317D1 ON tracker_history_jammer (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_DF97DAB35C585BB2 ON tracker_history_jammer (tracker_history_on_id)');
        $this->addSql('CREATE INDEX IDX_DF97DAB395F5DBB2 ON tracker_history_jammer (tracker_history_off_id)');
        $this->addSql('CREATE INDEX IDX_DF97DAB3C3423909 ON tracker_history_jammer (driver_id)');
        $this->addSql('CREATE INDEX IDX_DF97DAB3296CD8AE ON tracker_history_jammer (team_id)');
        $this->addSql('CREATE INDEX tracker_history_jammer_device_id_occurred_at_on_index ON tracker_history_jammer (device_id, occurred_at_on)');
        $this->addSql('CREATE INDEX tracker_history_jammer_vehicle_id_occurred_at_on_index ON tracker_history_jammer (vehicle_id, occurred_at_on)');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB394A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB3545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB35C585BB2 FOREIGN KEY (tracker_history_on_id) REFERENCES tracker_history (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB395F5DBB2 FOREIGN KEY (tracker_history_off_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB3C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_jammer ADD CONSTRAINT FK_DF97DAB3296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tracker_history_jammer');
    }
}
