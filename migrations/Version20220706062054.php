<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220706062054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_xero_account ALTER xero_tenant_id SET NOT NULL');
        $this->addSql('ALTER TABLE client_xero_account ADD CONSTRAINT FK_D420DA7EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D420DA7EA76ED395 ON client_xero_account (user_id)');
        $this->addSql('ALTER INDEX idx_4cb27adf297cd8ae RENAME TO IDX_D420DA7E296CD8AE');
        $this->addSql('DROP INDEX idx_4cb27adf296cd8ae');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F6806109296CD8AE ON client_xero_secret (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_F6806109296CD8AE');
        $this->addSql('CREATE INDEX idx_4cb27adf296cd8ae ON client_xero_secret (team_id)');
        $this->addSql('ALTER TABLE client_xero_account DROP CONSTRAINT FK_D420DA7EA76ED395');
        $this->addSql('DROP INDEX IDX_D420DA7EA76ED395');
        $this->addSql('ALTER TABLE client_xero_account ALTER xero_tenant_id DROP NOT NULL');
        $this->addSql('ALTER INDEX idx_d420da7e296cd8ae RENAME TO idx_4cb27adf297cd8ae');
    }
}
