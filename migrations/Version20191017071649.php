<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191017071649 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE area_history (id SERIAL NOT NULL, area_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, driver_id BIGINT DEFAULT NULL, arrived TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, departed TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_246A19B6BD0F409C ON area_history (area_id)');
        $this->addSql('CREATE INDEX IDX_246A19B6545317D1 ON area_history (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_246A19B6C3423909 ON area_history (driver_id)');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT FK_246A19B6BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT FK_246A19B6545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT FK_246A19B6C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE area_history');
    }
}
