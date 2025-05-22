<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615130920 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE reminder ADD asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F405DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_40374F405DA1941 ON reminder (asset_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE reminder DROP CONSTRAINT FK_40374F405DA1941');
        $this->addSql('DROP INDEX IDX_40374F405DA1941');
        $this->addSql('ALTER TABLE reminder DROP asset_id');
    }
}
