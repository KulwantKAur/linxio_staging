<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190910091526 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_route ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD driver_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD scope VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE device_route ADD CONSTRAINT FK_C441C01E545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_route ADD CONSTRAINT FK_C441C01EC3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C441C01E545317D1 ON device_route (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_C441C01EC3423909 ON device_route (driver_id)');

        $this->addSql('ALTER TABLE IF EXISTS device_route RENAME TO route');
        $this->addSql('ALTER SEQUENCE device_route_id_seq RENAME TO route_id_seq');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE IF EXISTS route RENAME TO device_route');
        $this->addSql('ALTER SEQUENCE route_id_seq RENAME TO device_route_id_seq');

        $this->addSql('ALTER TABLE device_route DROP scope');
        $this->addSql('ALTER TABLE device_route DROP comment');
        $this->addSql('ALTER TABLE device_route DROP CONSTRAINT FK_C441C01E545317D1');
        $this->addSql('ALTER TABLE device_route DROP CONSTRAINT FK_C441C01EC3423909');
        $this->addSql('DROP INDEX IDX_C441C01E545317D1');
        $this->addSql('DROP INDEX IDX_C441C01EC3423909');
        $this->addSql('ALTER TABLE device_route DROP vehicle_id');
        $this->addSql('ALTER TABLE device_route DROP driver_id');
    }
}
