<?php

namespace App\Resources\procedures;

/**
 * @example SELECT reset_table_sequence_add_job('tracker_payload');
    SELECT reset_table_sequence_tracker_payload();
 */
class ResetTableSequenceAddJob implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION reset_table_sequence_add_job(
    table_name TEXT
) RETURNS void AS
$func$
BEGIN
    RAISE INFO 'Function: reset_table_sequence_add_job';

    EXECUTE format('CREATE OR REPLACE FUNCTION reset_table_sequence_%1$s()
    RETURNS void AS $func2$
DECLARE
    _max_id INT;
    _max_value INT = 2100000000;
    _last_seq_value INT;
BEGIN
    SELECT MAX(id) FROM %1$s INTO _max_id;
    SELECT last_value FROM %1$s_id_seq INTO _last_seq_value;

    IF _max_id > _max_value AND _last_seq_value > _max_value THEN
        RAISE INFO ''Sequence will be reset: %1$s_id_seq'';
        ALTER SEQUENCE %1$s_id_seq RESTART WITH 1;
    END IF;

    SELECT last_value FROM %1$s_id_seq INTO _last_seq_value;
    RAISE INFO ''%1$s_id_seq: %%'', _last_seq_value;
END;
$func2$ LANGUAGE plpgsql;', table_name);

    EXECUTE format('SELECT cron.schedule(''reset_table_sequence_%1$s'', ''0 13 * * *'', $$SELECT reset_table_sequence_%1$s();$$);', table_name);
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}