<?php

namespace App\Resources\procedures;

class IntToBigintDropRelatedColumnsAndForeignKeys implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_add_related_columns_and_foreign_keys(
    table_name TEXT,
    column_name TEXT
) RETURNS VOID AS
$func$
DECLARE
    column_name_bigint TEXT;
    query_with_keys TEXT;
    foreign_column_name_new TEXT;
    foreign_constraint_name_new TEXT;
    r RECORD;
BEGIN
    RAISE INFO 'Function: int_to_bigint_add_related_columns_and_foreign_keys';
    column_name_bigint := column_name || '_bigint';
    query_with_keys := format('SELECT tc.constraint_name::TEXT AS constraint_name,
           tc.table_name::TEXT AS target_table_name,
           kcu.column_name::TEXT AS target_column_name
    FROM information_schema.table_constraints AS tc
             JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
             JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
    WHERE constraint_type = ''FOREIGN KEY''
      AND ccu.table_name = ''%1$s''
      AND ccu.column_name = ''%2$s'';', table_name, column_name);

    FOR r IN EXECUTE query_with_keys LOOP
        foreign_column_name_new := r.target_column_name || '_bigint';
        foreign_constraint_name_new := foreign_column_name_new || '_fkey';
        RAISE INFO 'Add for table % column %', r.target_table_name, foreign_column_name_new;
        EXECUTE format('ALTER TABLE %s ADD COLUMN %s BIGINT', r.target_table_name, foreign_column_name_new);
        RAISE INFO 'Add for table % constraint %', r.target_table_name, foreign_constraint_name_new;
        EXECUTE format('ALTER TABLE %1$s ADD CONSTRAINT %2$s FOREIGN KEY (%3$s) REFERENCES %4$s (%5$s) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;', r.target_table_name, foreign_constraint_name_new, foreign_column_name_new, table_name, column_name_bigint);
        END LOOP;
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}