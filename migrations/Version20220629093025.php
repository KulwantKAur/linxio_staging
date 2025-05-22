<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220629093025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX tracker_io_type_name_index ON tracker_io_type (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX tracker_io_type_name_index');
    }
}
