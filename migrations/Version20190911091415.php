<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911091415 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE area_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE area_group (id INT NOT NULL, team_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E00A0C1F296CD8AE ON area_group (team_id)');
        $this->addSql('CREATE INDEX IDX_E00A0C1FDE12AB56 ON area_group (created_by)');
        $this->addSql('CREATE INDEX IDX_E00A0C1F16FE72E1 ON area_group (updated_by)');
        $this->addSql('CREATE TABLE areas_groups (area_group_id INT NOT NULL, area_id INT NOT NULL, PRIMARY KEY(area_group_id, area_id))');
        $this->addSql('CREATE INDEX IDX_A457C0173D5B878F ON areas_groups (area_group_id)');
        $this->addSql('CREATE INDEX IDX_A457C017BD0F409C ON areas_groups (area_id)');
        $this->addSql('ALTER TABLE area_group ADD CONSTRAINT FK_E00A0C1F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_group ADD CONSTRAINT FK_E00A0C1FDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_group ADD CONSTRAINT FK_E00A0C1F16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE areas_groups ADD CONSTRAINT FK_A457C0173D5B878F FOREIGN KEY (area_group_id) REFERENCES area_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE areas_groups ADD CONSTRAINT FK_A457C017BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area ALTER coordinates SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE areas_groups DROP CONSTRAINT FK_A457C0173D5B878F');
        $this->addSql('DROP SEQUENCE area_group_id_seq CASCADE');
        $this->addSql('DROP TABLE area_group');
        $this->addSql('DROP TABLE areas_groups');
        $this->addSql('ALTER TABLE area ALTER coordinates DROP NOT NULL');
    }
}
