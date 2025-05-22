<?php

namespace App\Resources\procedures;

/**
 * @example SELECT int_to_bigint_final_clear_data('speeding', 'point_finish_id', false);
 * @example SELECT int_to_bigint_final_clear_data('tracker_payload', 'id', true, false);
 */
class IntToBigintFinalClearData implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_final_clear_data(
    table_name TEXT,
    column_name TEXT,
    is_primary_key BOOLEAN = false,
    is_not_null BOOLEAN = false
) RETURNS void AS
$func$
DECLARE
    column_name_bigint TEXT;
    column_name_old TEXT;
BEGIN
    RAISE INFO 'Function: int_to_bigint_final_clear_data';
    column_name_bigint := column_name || '_bigint';
    column_name_old := column_name || '_old';

    EXECUTE format('SELECT cron.unschedule(''int_to_bigint_populate_exist_%1$s_%2$s'');', table_name, column_name);

    IF is_primary_key THEN
        EXECUTE int_to_bigint_drop_related_foreign_keys(table_name, column_name);
        EXECUTE format('ALTER TABLE %1$s DROP CONSTRAINT %1$s_pkey;', table_name);
        EXECUTE format('ALTER TABLE %1$s ADD CONSTRAINT %1$s_pkey PRIMARY KEY USING INDEX %1$s_new_pkey;', table_name);
        EXECUTE format('ALTER TABLE %1$s ALTER COLUMN %2$s SET DEFAULT nextval(''%1$s_id_seq''::regclass);', table_name, column_name_bigint);
        EXECUTE format('ALTER TABLE %1$s ALTER COLUMN %2$s DROP DEFAULT;', table_name, column_name);
    END IF;

    EXECUTE format('ALTER TABLE %1$s ALTER COLUMN %2$s DROP NOT NULL;', table_name, column_name);
    EXECUTE format('ALTER TABLE %1$s RENAME COLUMN %2$s TO %3$s;', table_name, column_name, column_name_old);
    EXECUTE format('ALTER TABLE %1$s RENAME COLUMN %2$s TO %3$s;', table_name, column_name_bigint, column_name);
    EXECUTE format('DROP TRIGGER IF EXISTS int_to_bigint_populate_%1$s_%2$s_trigger ON %1$s;', table_name, column_name);
    EXECUTE format('DROP FUNCTION IF EXISTS int_to_bigint_populate_%1$s_%2$s();', table_name, column_name);

    IF is_not_null THEN
        EXECUTE format('ALTER TABLE %1$s ALTER COLUMN %2$s SET NOT NULL;', table_name, column_name);
    end if;
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}