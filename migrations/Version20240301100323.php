<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240301100323 extends AbstractMigration
{
    private function addFunctionToGetVehicleLastAccuracy()
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_v_last_accuracy(v_id bigint, th_date timestamp)
    RETURNS BIGINT AS
$function$
BEGIN
    RETURN (SELECT vo.accuracy
            FROM vehicle_odometer vo
            where vo.occurred_at < th_date
              and vo.accuracy is not null
              and vo.vehicle_id = v_id
            order by vo.occurred_at desc
            limit 1);
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
        $this->addSql($this->addFunctionToGetVehicleLastAccuracy());
    }

    public function down(Schema $schema): void
    {

    }
}
