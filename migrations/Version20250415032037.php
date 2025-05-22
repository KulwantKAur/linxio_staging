<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415032037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the unique constraint on driver_fob_id and add a composite unique constraint on driver_fob_id and team_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1483A5E9397D506A');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9397D506A296CD8AE ON users (driver_fob_id, team_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1483A5E9397D506A296CD8AE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9397D506A ON users (driver_fob_id)');
    }
}
