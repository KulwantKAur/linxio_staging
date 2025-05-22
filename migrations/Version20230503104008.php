<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503104008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reminder ALTER date_period TYPE BIGINT');
        $this->addSql('ALTER TABLE reminder ALTER mileage_period TYPE BIGINT');
        $this->addSql('ALTER TABLE reminder ALTER hours_period TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reminder ALTER date_period TYPE INT');
        $this->addSql('ALTER TABLE reminder ALTER mileage_period TYPE INT');
        $this->addSql('ALTER TABLE reminder ALTER hours_period TYPE INT');
    }
}
