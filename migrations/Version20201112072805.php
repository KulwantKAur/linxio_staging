<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201112072805 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX vehicle_odometer_device_id_occurred_at_index ON vehicle_odometer (device_id, occurred_at)');
        $this->addSql('CREATE INDEX vehicle_odometer_vehicle_id_occurred_at_index ON vehicle_odometer (vehicle_id, occurred_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX vehicle_odometer_device_id_occurred_at_index');
        $this->addSql('DROP INDEX vehicle_odometer_vehicle_id_occurred_at_index');
    }
}
