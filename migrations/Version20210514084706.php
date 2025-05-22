<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\DeviceSensorType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210514084706 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->makeAvailableDeviceSensorIbuttonType();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->makeUnavailableDeviceSensorIbuttonType();
    }

    private function makeAvailableDeviceSensorIbuttonType()
    {
        $this->addSql('UPDATE device_sensor_type SET is_available = true WHERE name = \''
            . DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE . '\'');
    }

    private function makeUnavailableDeviceSensorIbuttonType()
    {
        $this->addSql('UPDATE device_sensor_type SET is_available = false WHERE name = \''
            . DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE . '\'');
    }
}
