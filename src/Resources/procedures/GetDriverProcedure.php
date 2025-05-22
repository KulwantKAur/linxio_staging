<?php

namespace App\Resources\procedures;

class GetDriverProcedure implements InsertProcedureInterface
{
    /**
     * {@inheritDoc}
     */
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_driver(_vehicle_id integer, started_at timestamp)
    RETURNS TEXT AS
$function$
BEGIN
    RETURN (
        SELECT CONCAT(u.name, ' ', u.surname) as fullName
        FROM users u
                 LEFT JOIN driver_history dh ON u.id = dh.driver_id
        WHERE dh.vehicle_id = _vehicle_id
          AND dh.startdate <= started_at
          AND (dh.finishdate >= startdate OR dh.finishdate IS NULL)
        ORDER BY dh.startdate ASC
        LIMIT 1
    );
END
$function$ LANGUAGE plpgsql;
SQL;
    }
}
