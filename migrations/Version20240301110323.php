<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240301110323 extends AbstractMigration
{
    private function addFunctionToGetStartOdometer()
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_v_start_odometer(v_id bigint, start_date timestamp, finish_date timestamp)
    RETURNS BIGINT AS
$function$
BEGIN
    RETURN (select r.start_odometer from route r
            where (r.started_at <= finish_date) AND (r.finished_at >= start_date) and r.vehicle_id = v_id
            order by started_at limit 1);
END
$function$ LANGUAGE plpgsql;
SQL;
    }

    private function addFunctionToGetFinishOdometer()
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_v_finish_odometer(v_id bigint, start_date timestamp, finish_date timestamp)
    RETURNS BIGINT AS
$function$
BEGIN
    RETURN (select r.finish_odometer from route r
            where (r.started_at <= finish_date) AND (r.finished_at >= start_date) and r.vehicle_id = v_id
            order by finished_at desc limit 1);
END
$function$ LANGUAGE plpgsql;
SQL;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql($this->addFunctionToGetStartOdometer());
        $this->addSql($this->addFunctionToGetFinishOdometer());
    }

    public function down(Schema $schema): void
    {

    }
}
