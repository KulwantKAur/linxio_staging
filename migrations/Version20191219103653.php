<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191219103653 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema) : void
    {
         $this->addSql('DROP FUNCTION IF EXISTS get_idling_periods_from_driving_history(_vehicles_ids integer[], _dateFrom timestamp, _dateTo timestamp)');
         $this->addSql('DROP FUNCTION IF EXISTS get_parking_periods_from_driving_history(_vehicles_ids integer[], _dateFrom timestamp, _dateTo timestamp)');
         $this->addSql('CREATE INDEX route_type_started_at_finished_at_index ON route (type ASC, started_at DESC, finished_at DESC)');
         $this->addSql('CREATE INDEX idling_started_at_finished_at_index ON idling (started_at DESC, finished_at DESC)');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX route_type_started_at_finished_at_index');
        $this->addSql('DROP INDEX idling_started_at_finished_at_index');
    }
}
