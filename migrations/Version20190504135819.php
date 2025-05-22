<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190504135819 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE tracker_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_history (id INT NOT NULL, tracker_auth_id INT DEFAULT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, priority INT NOT NULL, lng NUMERIC(10, 8) NOT NULL, lat NUMERIC(11, 8) NOT NULL, alt DOUBLE PRECISION NOT NULL, angle DOUBLE PRECISION NOT NULL, satellites INT DEFAULT NULL, speed DOUBLE PRECISION DEFAULT NULL, payload TEXT NOT NULL, sensor_data TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA7D7DDFC70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_70E50DA7D7DDFC70 ON tracker_history (tracker_auth_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE tracker_history_id_seq CASCADE');
        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA7D7DDFC70');
        $this->addSql('DROP INDEX IDX_70E50DA7D7DDFC70');
        $this->addSql('DROP TABLE tracker_history');
    }
}
