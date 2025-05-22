<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190829152625 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP gps_status_duration');
        $this->addSql('ALTER TABLE client DROP stop_idling_duration');
        $this->addSql('ALTER TABLE client DROP stop_ignore_duration');
        $this->addSql('ALTER TABLE client DROP movement_ignore_duration');

        $this->addSql('ALTER TABLE device_route ADD total_stop_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD total_movement_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD total_idle_duration INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_route DROP total_stop_duration');
        $this->addSql('ALTER TABLE device_route DROP total_movement_duration');
        $this->addSql('ALTER TABLE device_route DROP total_idle_duration');

        $this->addSql('ALTER TABLE client ADD gps_status_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD stop_idling_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD stop_ignore_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD movement_ignore_duration INT DEFAULT NULL');
    }
}
