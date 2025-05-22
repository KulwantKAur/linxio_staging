<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304142930 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE assets_groups (vehicle_group_id INT NOT NULL, asset_id INT NOT NULL, PRIMARY KEY(vehicle_group_id, asset_id))');
        $this->addSql('CREATE INDEX IDX_C469E8BC2346D6D3 ON assets_groups (vehicle_group_id)');
        $this->addSql('CREATE INDEX IDX_C469E8BC5DA1941 ON assets_groups (asset_id)');
        $this->addSql('ALTER TABLE assets_groups ADD CONSTRAINT FK_C469E8BC2346D6D3 FOREIGN KEY (vehicle_group_id) REFERENCES vehicle_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assets_groups ADD CONSTRAINT FK_C469E8BC5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset ADD depot_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C8510D4DE FOREIGN KEY (depot_id) REFERENCES vehicle_depot (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2AF5A5C8510D4DE ON asset (depot_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE assets_groups');
        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5C8510D4DE');
        $this->addSql('DROP INDEX IDX_2AF5A5C8510D4DE');
        $this->addSql('ALTER TABLE asset DROP depot_id');
    }
}
