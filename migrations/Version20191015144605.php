<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191015144605 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card ADD is_show_time BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD driver_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C2C3423909 FOREIGN KEY (driver_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_66BC0C2C3423909 ON fuel_card (driver_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card DROP is_show_time');
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C2C3423909');
        $this->addSql('DROP INDEX IDX_66BC0C2C3423909');
        $this->addSql('ALTER TABLE fuel_card DROP driver_id');
    }
}
