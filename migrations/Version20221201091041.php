<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201091041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP INDEX uniq_f4ef95d9296cd8ae');
        $this->addSql('ALTER TABLE stripe_secret ALTER team_id DROP NOT NULL');
        $this->addSql('CREATE INDEX IDX_F4EF95D9296CD8AE ON stripe_secret (team_id)');
        $this->addSql('ALTER INDEX idx_d420da7e19eb6921 RENAME TO IDX_8BD424D319EB6921');
        $this->addSql('DROP INDEX uniq_f6806109296cd8ae');
        $this->addSql('CREATE INDEX IDX_F7CB4432296CD8AE ON xero_secret (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_F4EF95D9296CD8AE');
        $this->addSql('ALTER TABLE stripe_secret ALTER team_id SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_f4ef95d9296cd8ae ON stripe_secret (team_id)');
        $this->addSql('ALTER INDEX idx_8bd424d319eb6921 RENAME TO idx_d420da7e19eb6921');
        $this->addSql('DROP INDEX IDX_F7CB4432296CD8AE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f6806109296cd8ae ON xero_secret (team_id)');
    }
}
