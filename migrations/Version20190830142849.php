<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190830142849 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE fuel_card_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fuel_card (id INT NOT NULL, vehicle_id INT DEFAULT NULL, fuel_card_number VARCHAR(255) NOT NULL, refueled DOUBLE PRECISION NOT NULL, total DOUBLE PRECISION NOT NULL, fuel_price DOUBLE PRECISION NOT NULL, petrol_station VARCHAR(255) NOT NULL, refueled_fuel_type_id INT NOT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_66BC0C2545317D1 ON fuel_card (vehicle_id)');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C2545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE fuel_card_id_seq CASCADE');
        $this->addSql('DROP TABLE fuel_card');
    }
}
