<?php

namespace App\Resources\procedures;

class RouteAreaId implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_area_id_by_ts_vehicle(ts timestamp, vehicleid bigint)
    RETURNS text AS
$function$
BEGIN
    RETURN (SELECT STRING_AGG(DISTINCT a.id::text, ', ')
            FROM area_history ah
                     left join area a on a.id = ah.area_id
            where ah.vehicle_id = vehicleid
              AND (ts between ah.arrived and ah.departed OR (ah.departed IS NULL AND ts > ah.arrived)));
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}