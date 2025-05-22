<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210512143917 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sensor ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sensor ADD CONSTRAINT FK_BC8617B0296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->setSensorsTeamByCreated();
        $this->setSensorsTeamByDeviceSensor();
        $this->addSql('ALTER TABLE sensor ALTER team_id SET NOT NULL');
        $this->addSql('CREATE INDEX IDX_BC8617B0296CD8AE ON sensor (team_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX IDX_BC8617B0296CD8AE');
        $this->addSql('ALTER TABLE sensor DROP team_id');
    }

    private function setSensorsTeamByCreated()
    {
        $this->addSql('UPDATE sensor SET team_id = subquery.u_team_id FROM (
                SELECT s.id AS s_id, u.team_id AS u_team_id FROM sensor s 
                LEFT JOIN users u ON u.id = s.created_by 
                WHERE s.created_by IS NOT NULL
            ) AS subquery 
            WHERE sensor.id = subquery.s_id');
    }

    private function setSensorsTeamByDeviceSensor()
    {
        $this->addSql('UPDATE sensor SET team_id = subquery.ds_team_id FROM (
                SELECT s.id AS s_id, ds.team_id AS ds_team_id FROM sensor s 
                LEFT JOIN device_sensor ds ON ds.sensor_id = s.id 
                WHERE s.created_by IS NULL
            ) AS subquery 
            WHERE sensor.id = subquery.s_id');
    }
}
