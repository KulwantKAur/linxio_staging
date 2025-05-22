<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121165817 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
            '
DROP FUNCTION IF EXISTS get_excessive_speed_periods(
    excessive_speed_map JSON, 
    select_date_start TIMESTAMP, 
    select_date_end TIMESTAMP, 
    vehicle_ids INT[]
);'
        );
        $this->addSql(
            '
DROP FUNCTION IF EXISTS get_idling_periods (
    select_date_start TIMESTAMP,
    select_date_end TIMESTAMP,
    vehicle_ids INT []
);'
        );
    }

    public function down(Schema $schema) : void
    {
    }
}
