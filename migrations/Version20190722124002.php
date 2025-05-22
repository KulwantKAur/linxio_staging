<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190722124002 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE tracker_simulator_track_payload_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tracker_simulator_track_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_simulator_track_payload (id INT NOT NULL, simulator_track_id INT DEFAULT NULL, payload TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_94F0126AA6FC03CF ON tracker_simulator_track_payload (simulator_track_id)');
        $this->addSql('CREATE TABLE tracker_simulator_track (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, number INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tracker_simulator_track_payload ADD CONSTRAINT FK_94F0126AA6FC03CF FOREIGN KEY (simulator_track_id) REFERENCES tracker_simulator_track (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_simulator_track_payload DROP CONSTRAINT FK_94F0126AA6FC03CF');
        $this->addSql('DROP SEQUENCE tracker_simulator_track_payload_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tracker_simulator_track_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_simulator_track_payload');
        $this->addSql('DROP TABLE tracker_simulator_track');
    }
}
