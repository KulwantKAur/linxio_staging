<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200730145501 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT FK_70E50DA710DB296A');
        $this->addSql('ALTER TABLE tracker_history DROP priority');
        $this->addSql('ALTER TABLE tracker_history DROP satellites');
        $this->addSql('ALTER TABLE tracker_history DROP gsm_signal');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT FK_70E50DA710DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last DROP priority');
        $this->addSql('ALTER TABLE tracker_history_last DROP satellites');
        $this->addSql('ALTER TABLE tracker_history_last DROP gsm_signal');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT fk_70e50da710db296a');
        $this->addSql('ALTER TABLE tracker_history ADD priority INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD satellites INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD gsm_signal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT fk_70e50da710db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_last ADD priority INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ADD satellites INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ADD gsm_signal INT DEFAULT NULL');
    }
}
