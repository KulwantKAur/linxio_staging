<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128081518 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE fuel_card_temporary_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fuel_card_temporary (id INT NOT NULL, comments JSON DEFAULT NULL, vehicle_original VARCHAR(255) DEFAULT NULL, refueled_fuel_type_original VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN fuel_card_temporary.comments IS \'(DC2Type:json_array)\'');
        $this->addSql('DROP INDEX uniq_e6da5e2c5e237e066a70fe35');
        $this->addSql('ALTER TABLE fuel_mapping ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C290FE3E38');
        $this->addSql('ALTER TABLE fuel_card ADD fuel_card_temporary_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card DROP comments');
        $this->addSql('ALTER TABLE fuel_card DROP vehicle_original');
        $this->addSql('ALTER TABLE fuel_card DROP refueled_fuel_type_original');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C21DBE3F8F FOREIGN KEY (fuel_card_temporary_id) REFERENCES fuel_card_temporary (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C290FE3E38 FOREIGN KEY (refueled_fuel_type_id) REFERENCES fuel_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_66BC0C21DBE3F8F ON fuel_card (fuel_card_temporary_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C21DBE3F8F');
        $this->addSql('DROP SEQUENCE fuel_card_temporary_id_seq CASCADE');
        $this->addSql('DROP TABLE fuel_card_temporary');
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT fk_66bc0c290fe3e38');
        $this->addSql('DROP INDEX UNIQ_66BC0C21DBE3F8F');
        $this->addSql('ALTER TABLE fuel_card ADD comments JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD vehicle_original VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD refueled_fuel_type_original VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card DROP fuel_card_temporary_id');
        $this->addSql('COMMENT ON COLUMN fuel_card.comments IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT fk_66bc0c290fe3e38 FOREIGN KEY (refueled_fuel_type_id) REFERENCES fuel_mapping (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_mapping ALTER status SET DEFAULT \'active\'');
        $this->addSql('CREATE UNIQUE INDEX uniq_e6da5e2c5e237e066a70fe35 ON fuel_mapping (name, fuel_type_id)');
    }
}
