<?php

namespace App\Resources\procedures;

class IntToBigintDropRelatedForeignKeys implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION int_to_bigint_drop_related_foreign_keys(
    table_name TEXT,
    column_name TEXT
) RETURNS VOID AS
$func$
DECLARE
    column_name_bigint TEXT;
    query_with_keys TEXT;
    r RECORD;
BEGIN
    RAISE INFO 'Function: int_to_bigint_drop_related_foreign_keys';
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
        RAISE INFO 'Dropping from table % constraint %', r.target_table_name, r.constraint_name;
        EXECUTE format('ALTER TABLE %1$s DROP CONSTRAINT %2$s', r.target_table_name, r.constraint_name);
        RAISE INFO 'You have to add for table % constraint % with next SQL:', r.target_table_name, r.constraint_name;
        RAISE INFO 'ALTER TABLE % ADD CONSTRAINT % FOREIGN KEY (%) REFERENCES % (%) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;', r.target_table_name, r.constraint_name, r.target_column_name, table_name, column_name;
    END LOOP;
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}