<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209124101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'INT to BIGINT part 2';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE idling ALTER point_start_id TYPE BIGINT');
        $this->addSql('ALTER TABLE idling ALTER point_finish_id TYPE BIGINT');
        $this->addSql('ALTER TABLE speeding ALTER point_start_id TYPE BIGINT');
        $this->addSql('ALTER TABLE speeding ALTER point_finish_id TYPE BIGINT');
        $this->addSql('ALTER TABLE route ALTER point_start_id TYPE BIGINT');
        $this->addSql('ALTER TABLE route ALTER point_finish_id TYPE BIGINT');
        $this->addSql('ALTER TABLE route_temp ALTER point_start_id TYPE BIGINT');
        $this->addSql('ALTER TABLE route_temp ALTER point_finish_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_temp ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_io ALTER tracker_history_off_id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_io ALTER tracker_history_off_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_temp ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE idling ALTER point_start_id TYPE INT');
        $this->addSql('ALTER TABLE idling ALTER point_finish_id TYPE INT');
        $this->addSql('ALTER TABLE speeding ALTER point_start_id TYPE INT');
        $this->addSql('ALTER TABLE speeding ALTER point_finish_id TYPE INT');
        $this->addSql('ALTER TABLE route ALTER point_start_id TYPE INT');
        $this->addSql('ALTER TABLE route ALTER point_finish_id TYPE INT');
        $this->addSql('ALTER TABLE route_temp ALTER point_start_id TYPE INT');
        $this->addSql('ALTER TABLE route_temp ALTER point_finish_id TYPE INT');
    }
}
