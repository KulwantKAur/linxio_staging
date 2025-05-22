<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728150400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX route_started_at_id_idx');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE INDEX route_started_at_id_idx ON route (started_at DESC, id DESC)');
    }
}
