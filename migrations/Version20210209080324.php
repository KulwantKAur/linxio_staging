<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Team;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209080324 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666AB296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F9666AB296CD8AE ON device_sensor (team_id)');
        $this->updateSensorTeam();
        $this->addSql('ALTER TABLE device_sensor ALTER team_id SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666AB296CD8AE');
        $this->addSql('DROP INDEX IDX_6F9666AB296CD8AE');
        $this->addSql('ALTER TABLE device_sensor DROP team_id');
    }

    private function updateSensorTeam()
    {
        $this->addSql('UPDATE device_sensor SET team_id = device_sub.d_team_id
            FROM (SELECT ds.id AS ds_id, d.team_id AS d_team_id
                FROM device_sensor ds
                    LEFT JOIN device d ON ds.device_id = d.id
            ) AS device_sub
            WHERE device_sensor.id = device_sub.ds_id'
        );

        $this->addSql('UPDATE device_sensor SET team_id = team_sub.team_id
            FROM (SELECT t.id AS team_id
                FROM team t WHERE t.type = \''. Team::TEAM_ADMIN .'\' LIMIT 1
            ) AS team_sub
            WHERE device_sensor.device_id IS NULL AND device_sensor.team_id IS NULL'
        );
    }
}
