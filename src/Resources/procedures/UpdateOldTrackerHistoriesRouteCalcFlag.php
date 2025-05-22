<?php
namespace App\Resources\procedures;

class UpdateOldTrackerHistoriesRouteCalcFlag implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION update_old_tracker_histories_route_calc_flags(
    date DATE,
    records_limit INT = 10000,
    loops INT = 10
)
    RETURNS SETOF bigint AS
$func$
BEGIN
    RETURN QUERY
        SELECT id
        FROM tracker_history
        WHERE is_calculated = false
            AND ts < date
        LIMIT 1;

    IF NOT FOUND THEN
        RETURN;
    END IF;

    FOR i IN 1..loops LOOP
        RETURN QUERY
            UPDATE tracker_history
                SET is_calculated = true
                WHERE id IN (
                    SELECT id
                    FROM (
                             SELECT id
                             FROM tracker_history
                             WHERE is_calculated = false
                               AND ts < date
                             LIMIT records_limit
                         ) subquery
                )
                RETURNING id;
        END LOOP;
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}