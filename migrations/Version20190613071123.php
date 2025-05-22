<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190613071123 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle RENAME COLUMN depot TO depot_id');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4868510D4DE FOREIGN KEY (depot_id) REFERENCES depot (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E4868510D4DE ON vehicle (depot_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E4868510D4DE');
        $this->addSql('DROP INDEX IDX_1B80E4868510D4DE');
        $this->addSql('ALTER TABLE vehicle RENAME COLUMN depot_id TO depot');
    }
}
