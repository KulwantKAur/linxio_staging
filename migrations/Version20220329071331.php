<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329071331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billing_entity_history ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_entity_history ADD CONSTRAINT FK_3CB27ADF296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3CB27ADF296CD8AE ON billing_entity_history (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billing_entity_history DROP CONSTRAINT FK_3CB27ADF296CD8AE');
        $this->addSql('DROP INDEX IDX_3CB27ADF296CD8AE');
        $this->addSql('ALTER TABLE billing_entity_history DROP team_id');
    }
}
