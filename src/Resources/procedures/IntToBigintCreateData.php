<?php

namespace App\Resources\procedures;

/**
 * @example SELECT int_to_bigint_create_data('speeding', 'point_finish_id', false, false, false, 'tracker_history', 'id', false, 100000);
 * @example SELECT int_to_bigint_create_data('tracker_payload', 'id', true, true, false, null, null, false, 100000);
 */
class IntToBigintCreateData implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_create_data(
    table_name TEXT,
    column_name TEXT,
    is_primary_key BOOLEAN = false,
    is_uniq_column BOOLEAN = false,
    add_foreign_key BOOLEAN = false,
    ref_fkey_table_name TEXT = 'tracker_history',
    ref_fkey_column_name TEXT = 'id',
    is_cascade_fkey BOOLEAN = false,
    _limit INT = 1000
) RETURNS void AS
$func$
DECLARE
    column_name_bigint TEXT;
BEGIN
    RAISE INFO 'Function: int_to_bigint_create_data';
    column_name_bigint := column_name || '_bigint';

    EXECUTE int_to_bigint_create_columns_and_keys(table_name, column_name, is_primary_key, is_uniq_column, add_foreign_key, ref_fkey_table_name, ref_fkey_column_name, is_cascade_fkey);
    EXECUTE format('CREATE OR REPLACE FUNCTION int_to_bigint_populate_%1$s_%2$s()
    RETURNS trigger AS $func2$
BEGIN
    NEW.%3$s := NEW.%2$s;
    RETURN NEW;
END;
$func2$ LANGUAGE plpgsql;', table_name, column_name, column_name_bigint);
    EXECUTE format('CREATE TRIGGER int_to_bigint_populate_%1$s_%2$s_trigger BEFORE INSERT OR UPDATE ON %1$s FOR EACH ROW EXECUTE PROCEDURE int_to_bigint_populate_%1$s_%2$s(%1$s, %2$s);', table_name, column_name);
    EXECUTE int_to_bigint_populate_exist(table_name, column_name, _limit);
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}