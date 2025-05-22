<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190919141118 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE fuel_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fuel_mark_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fuel_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9CA10F385E237E06 ON fuel_type (name)');
        $this->addSql('CREATE TABLE fuel_mark (id INT NOT NULL, fuel_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_760BAA605E237E06 ON fuel_mark (name)');
        $this->addSql('CREATE INDEX IDX_760BAA606A70FE35 ON fuel_mark (fuel_type_id)');
        $this->addSql('ALTER TABLE fuel_mark ADD CONSTRAINT FK_760BAA606A70FE35 FOREIGN KEY (fuel_type_id) REFERENCES fuel_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_card ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD comments JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_card_number DROP NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER refueled DROP NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER total DROP NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_price DROP NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER petrol_station DROP NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER refueled_fuel_type_id DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN fuel_card.comments IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C293CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C290FE3E38 FOREIGN KEY (refueled_fuel_type_id) REFERENCES fuel_mark (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_66BC0C293CB796C ON fuel_card (file_id)');
        $this->addSql('CREATE INDEX IDX_66BC0C290FE3E38 ON fuel_card (refueled_fuel_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fuel_mark DROP CONSTRAINT FK_760BAA606A70FE35');
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C290FE3E38');
        $this->addSql('DROP SEQUENCE fuel_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fuel_mark_id_seq CASCADE');
        $this->addSql('DROP TABLE fuel_type');
        $this->addSql('DROP TABLE fuel_mark');
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C293CB796C');
        $this->addSql('DROP INDEX IDX_66BC0C293CB796C');
        $this->addSql('DROP INDEX IDX_66BC0C290FE3E38');
        $this->addSql('ALTER TABLE fuel_card DROP file_id');
        $this->addSql('ALTER TABLE fuel_card DROP comments');
        $this->addSql('ALTER TABLE fuel_card DROP status');
        $this->addSql('ALTER TABLE fuel_card ALTER refueled_fuel_type_id SET NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_card_number SET NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER refueled SET NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER total SET NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER fuel_price SET NOT NULL');
        $this->addSql('ALTER TABLE fuel_card ALTER petrol_station SET NOT NULL');
    }
}
