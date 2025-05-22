<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190705110628 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE driver_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE driver_history (id INT NOT NULL, team_id BIGINT NOT NULL, vehicle_id INT NOT NULL, startDate TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finishDate TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E245142B296CD8AE ON driver_history (team_id)');
        $this->addSql('CREATE INDEX IDX_E245142B545317D1 ON driver_history (vehicle_id)');
        $this->addSql('ALTER TABLE driver_history ADD CONSTRAINT FK_E245142B296CD8AE FOREIGN KEY (team_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver_history ADD CONSTRAINT FK_E245142B545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle ADD driver_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E486C3423909 ON vehicle (driver_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE driver_history_id_seq CASCADE');
        $this->addSql('DROP TABLE driver_history');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486C3423909');
        $this->addSql('DROP INDEX IDX_1B80E486C3423909');
        $this->addSql('ALTER TABLE vehicle DROP driver_id');
    }
}
