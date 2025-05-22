<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190517123049 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE vehicle_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle_group (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE vehicles_groups (vehicle_group_id INT NOT NULL, vehicle_id INT NOT NULL, PRIMARY KEY(vehicle_group_id, vehicle_id))');
        $this->addSql('CREATE INDEX IDX_B8F4F4852346D6D3 ON vehicles_groups (vehicle_group_id)');
        $this->addSql('CREATE INDEX IDX_B8F4F485545317D1 ON vehicles_groups (vehicle_id)');
        $this->addSql('ALTER TABLE vehicles_groups ADD CONSTRAINT FK_B8F4F4852346D6D3 FOREIGN KEY (vehicle_group_id) REFERENCES vehicle_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicles_groups ADD CONSTRAINT FK_B8F4F485545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_group ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_group ADD CONSTRAINT FK_F6FC42919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F6FC42919EB6921 ON vehicle_group (client_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE vehicle_group DROP CONSTRAINT FK_F6FC42919EB6921');
        $this->addSql('DROP INDEX IDX_F6FC42919EB6921');
        $this->addSql('ALTER TABLE vehicle_group DROP client_id');
        $this->addSql('ALTER TABLE vehicles_groups DROP CONSTRAINT FK_B8F4F4852346D6D3');
        $this->addSql('DROP SEQUENCE vehicle_group_id_seq CASCADE');
        $this->addSql('DROP TABLE vehicle_group');
        $this->addSql('DROP TABLE vehicles_groups');
    }
}
