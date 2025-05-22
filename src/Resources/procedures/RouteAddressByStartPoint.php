<?php

namespace App\Resources\procedures;

class RouteAddressByStartPoint implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_route_start_address(pointstart_id bigint, deviceid bigint)
    RETURNS text AS
$function$
BEGIN
    RETURN (SELECT r.address         AS address_from
                                    FROM route r
                                    WHERE r.type = 'stopped' and r.point_finish_id = pointstart_id and r.device_id = deviceid limit 1);
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}