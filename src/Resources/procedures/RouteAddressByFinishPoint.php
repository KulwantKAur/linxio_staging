<?php

namespace App\Resources\procedures;

class RouteAddressByFinishPoint implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_route_finish_address(pointfinish_id bigint, deviceid bigint)
    RETURNS text AS
$function$
BEGIN
    RETURN (SELECT r.address         AS address_to
                                    FROM route r
                                    WHERE r.type = 'stopped' and r.point_start_id = pointfinish_id and r.device_id = deviceid limit 1);
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}