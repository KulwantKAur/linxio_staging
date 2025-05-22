<?php

namespace App\Resources\procedures;

/**
 * @example SELECT reset_table_sequence_clear_job('tracker_payload');
 */
class ResetTableSequenceClearJob implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION reset_table_sequence_clear_job(
    table_name TEXT
) RETURNS void AS
$func$
BEGIN
    RAISE INFO 'Function: reset_table_sequence_clear_job';
    EXECUTE format('SELECT cron.unschedule(''reset_table_sequence_%1$s'');', table_name);
    EXECUTE format('DROP FUNCTION IF EXISTS reset_table_sequence_%1$s();', table_name);
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}