<?php

namespace App\Resources\procedures;

class IntToBigintPopulateExistingData implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_populate_exist(
    table_name TEXT,
    column_name TEXT,
    _limit INT = 1000
) RETURNS void AS
$func$
DECLARE
    _column_name_bigint TEXT;
BEGIN
    RAISE INFO 'Function: int_to_bigint_populate_exist';
    _column_name_bigint := column_name || '_bigint';

    EXECUTE format('SELECT cron.schedule(''int_to_bigint_populate_exist_%1$s_%3$s'', ''* * * * *'', $$UPDATE %1$s SET %2$s = %3$s WHERE id IN (SELECT id FROM %1$s WHERE %2$s IS NULL AND %3$s IS NOT NULL LIMIT %4$s);$$);', table_name, _column_name_bigint, column_name, _limit);
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}