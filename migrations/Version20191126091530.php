<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191126091530 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP FUNCTION IF EXISTS get_idling_periods_from_tracker_history_by_team_id(_team_id INTEGER)');
        $this->addSql('DROP FUNCTION IF EXISTS get_parking_periods_from_tracker_history_by_team_id(_team_id INTEGER)');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema) : void
    {
    }
}
