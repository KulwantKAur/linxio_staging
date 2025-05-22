<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210218114912 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE vehicles_drivers');
        $this->addSql('ALTER TABLE users ADD sensor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9A247991F ON users (sensor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE vehicles_drivers (vehicle_id INT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(vehicle_id, user_id))');
        $this->addSql('CREATE INDEX idx_298f5c59a76ed395 ON vehicles_drivers (user_id)');
        $this->addSql('CREATE INDEX idx_298f5c59545317d1 ON vehicles_drivers (vehicle_id)');
        $this->addSql('ALTER TABLE vehicles_drivers ADD CONSTRAINT fk_298f5c59545317d1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicles_drivers ADD CONSTRAINT fk_298f5c59a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9A247991F');
        $this->addSql('DROP INDEX UNIQ_1483A5E9A247991F');
        $this->addSql('ALTER TABLE users DROP sensor_id');
    }
}
