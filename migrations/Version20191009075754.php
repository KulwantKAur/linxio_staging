<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191009075754 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle ADD fuel_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle DROP fueltype');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4866A70FE35 FOREIGN KEY (fuel_type_id) REFERENCES fuel_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E4866A70FE35 ON vehicle (fuel_type_id)');
        $this->addSql('ALTER TABLE fuel_card ADD vehicle_original VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD refueled_fuel_type_original VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E4866A70FE35');
        $this->addSql('DROP INDEX IDX_1B80E4866A70FE35');
        $this->addSql('ALTER TABLE vehicle ADD fueltype VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle DROP fuel_type_id');
        $this->addSql('ALTER TABLE fuel_card DROP vehicle_original');
        $this->addSql('ALTER TABLE fuel_card DROP refueled_fuel_type_original');
    }
}
