<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190404162612 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP COLUMN timezone');
        $this->addSql('ALTER TABLE client ADD COLUMN timezone INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_client_timezone_time_zone_id FOREIGN KEY (timezone) REFERENCES time_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_client_timezone_time_zone_id');
        $this->addSql('ALTER TABLE client DROP COLUMN timezone');
        $this->addSql('ALTER TABLE client ADD COLUMN timezone VARCHAR(50) DEFAULT NULL');
    }
}
