<?php

namespace App\Resources\procedures;

class VehicleDistanceByTH implements InsertProcedureInterface
{
    /**
     * {@inheritDoc}
     */
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION th_vehicle_distance(veh_id INT, date_from TIMESTAMP, date_to TIMESTAMP) RETURNS BIGINT AS
$function$
BEGIN
    RETURN ((SELECT odometer
             FROM tracker_history
             WHERE ts between date_from and date_to
               AND vehicle_id = veh_id
               and odometer is not null
             ORDER BY ts DESC
             LIMIT 1) -
            (SELECT odometer
             FROM tracker_history
             WHERE ts between date_from and date_to
               AND vehicle_id = veh_id
               and odometer is not null
             ORDER BY ts ASC
             LIMIT 1));
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}
