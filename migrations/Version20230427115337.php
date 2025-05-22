<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230427115337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fuel_card ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD CONSTRAINT FK_66BC0C216FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_66BC0C216FE72E1 ON fuel_card (updated_by)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fuel_card DROP CONSTRAINT FK_66BC0C216FE72E1');
        $this->addSql('DROP INDEX IDX_66BC0C216FE72E1');
        $this->addSql('ALTER TABLE fuel_card DROP updated_by');
        $this->addSql('ALTER TABLE fuel_card DROP updated_at');
    }
}
