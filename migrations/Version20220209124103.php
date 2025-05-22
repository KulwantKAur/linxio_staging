<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209124103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'INT to BIGINT part 4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_io ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_io_last ALTER tracker_history_io_id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_io_last ALTER tracker_history_io_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_io ALTER id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_sensor ALTER id TYPE INT');
    }
}
