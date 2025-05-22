<?php

namespace App\Resources\procedures;

class DriverDistanceByTH implements InsertProcedureInterface
{
    /**
     * {@inheritDoc}
     */
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION th_driver_distance(dri_id BIGINT, date_from TIMESTAMP, date_to TIMESTAMP) RETURNS INT AS
$function$
BEGIN
    RETURN ((SELECT odometer
             FROM tracker_history
             WHERE ts between date_from and date_to
               AND driver_id = dri_id
               and odometer is not null
             ORDER BY ts DESC
             LIMIT 1) -
            (SELECT odometer
             FROM tracker_history
             WHERE ts between date_from and date_to
               AND driver_id = dri_id
               and odometer is not null
             ORDER BY ts ASC
             LIMIT 1));
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}
