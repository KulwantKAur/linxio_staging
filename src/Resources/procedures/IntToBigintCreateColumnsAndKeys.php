<?php

namespace App\Resources\procedures;

class IntToBigintCreateColumnsAndKeys implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_create_columns_and_keys(
    table_name TEXT,
    column_name TEXT,
    is_primary_key BOOLEAN = false,
    is_uniq_column BOOLEAN = true,
    add_foreign_key BOOLEAN = false,
    ref_fkey_table_name TEXT = 'tracker_history',
    ref_fkey_column_name TEXT = 'id',
    is_cascade_fkey BOOLEAN = false
) RETURNS void AS
$func$
DECLARE
    _column_name_bigint TEXT;
    _fkey_behavior TEXT;
BEGIN
    RAISE INFO 'Function: int_to_bigint_create_columns_and_keys';
    _column_name_bigint := column_name || '_bigint';

    IF is_primary_key THEN
        EXECUTE format('ALTER TABLE %s ADD COLUMN %s BIGINT', table_name, _column_name_bigint);
        EXECUTE format('CREATE UNIQUE INDEX %1$s_new_pkey ON %1$s (%2$s);', table_name, _column_name_bigint);
--         EXECUTE int_to_bigint_add_related_columns_and_foreign_keys(table_name, column_name);
    ELSE
        IF is_cascade_fkey THEN
            _fkey_behavior := 'CASCADE';
        ELSE
            _fkey_behavior := 'SET NULL';
        END IF;

        EXECUTE format('ALTER TABLE %s ADD COLUMN %s BIGINT', table_name, _column_name_bigint);

        IF add_foreign_key THEN
            EXECUTE format('ALTER TABLE %1$s ADD CONSTRAINT %1$s_%2$s_fkey FOREIGN KEY (%2$s) REFERENCES %3$s (%4$s) ON DELETE %5$s NOT DEFERRABLE INITIALLY IMMEDIATE;', table_name, _column_name_bigint, ref_fkey_table_name, ref_fkey_column_name, _fkey_behavior);
        END IF;

        IF is_uniq_column THEN
            EXECUTE format('CREATE UNIQUE INDEX uniq_%1$s_new_index ON %1$s (%2$s);', table_name, _column_name_bigint);
        ELSE
            EXECUTE format('CREATE INDEX %1$s_%2$s_index ON %1$s (%2$s)', table_name, _column_name_bigint);
        END IF;
    END IF;
END;
$func$ LANGUAGE plpgsql;
SQL;
    }
}