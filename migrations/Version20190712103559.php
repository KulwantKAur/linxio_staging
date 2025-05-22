<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190712103559 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE area_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE area (id INT NOT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, polygon geometry(GEOMETRY, 0) NOT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D7943D68296CD8AE ON area (team_id)');
        $this->addSql('CREATE INDEX IDX_D7943D68DE12AB56 ON area (created_by)');
        $this->addSql('CREATE INDEX IDX_D7943D6816FE72E1 ON area (updated_by)');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D68296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D68DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D6816FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area ADD coordinates JSON');
        $this->addSql('ALTER TABLE area ADD status VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE area_id_seq CASCADE');
        $this->addSql('DROP TABLE area');
    }
}
