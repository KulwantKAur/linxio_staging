<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210312100916 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor ADD rssi SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5CA247991F');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5CA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE asset DROP CONSTRAINT fk_2af5a5ca247991f');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT fk_2af5a5ca247991f FOREIGN KEY (sensor_id) REFERENCES sensor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_sensor DROP rssi');
    }
}
