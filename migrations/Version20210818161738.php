<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818161738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reminder_category RENAME COLUMN "order" TO sort');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reminder_category RENAME COLUMN sort TO "order"');
    }
}
