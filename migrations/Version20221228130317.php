<?php
declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228130317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT invoice_owner_team_id');
        $this->addSql('ALTER TABLE invoice DROP owner_team_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD owner_team_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT invoice_owner_team_id FOREIGN KEY (owner_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}