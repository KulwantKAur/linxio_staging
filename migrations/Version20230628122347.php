<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628122347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device DROP parser_type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device ADD parser_type SMALLINT DEFAULT NULL');
    }
}
