<?php

namespace App\Resources\procedures;

/**
 * @example SELECT delete_partitions('table_name', 'table_short_name', '1 month', 'YYYY_MM');
 * @example SELECT delete_partitions('tracker_history_temp_part', 'thtp', '1 day', 'YYYY_MM_DD', 3, 2); // will delete 2 days that are started 3 days ago
 */
class DeletePartitions implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION delete_partitions(
    table_name TEXT,
    table_short_name TEXT,
    interval_name TEXT = '1 month',
    partition_name_format TEXT = 'YYYY_MM',
    offset_to_back INT = 12,
    count_to_del INT = 12
) RETURNS SETOF TEXT AS
$func$
DECLARE
    _partition_date TEXT;
    _partition TEXT;
    _prev_date date;
BEGIN
    FOR i IN offset_to_back - count_to_del..offset_to_back-1
        LOOP
            _prev_date := now() - i * (interval_name)::INTERVAL;
            _partition_date := to_char(_prev_date, partition_name_format);
            _partition := table_short_name || '_' || _partition_date;

            IF EXISTS(SELECT relname FROM pg_class WHERE relname=_partition) THEN
                EXECUTE 'DROP TABLE ' || _partition;
                RAISE NOTICE 'A partition has been deleted %', _partition;
            ELSE
                RAISE NOTICE 'A partition does not exist %', _partition;
            END IF;

            RETURN NEXT _partition;
        END LOOP;
    RETURN;
END;
$func$ LANGUAGE plpgsql;
SQL;
    }
}