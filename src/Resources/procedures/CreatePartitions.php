<?php

namespace App\Resources\procedures;

/**
 * @example SELECT create_partitions('table_name', 'table_short_name', '1 month', 'month', 'YYYY_MM');
 * @example SELECT create_partitions('tracker_history_temp_part', 'thtp', '1 day', 'day', 'YYYY_MM_DD', 4, 0, ARRAY['tracker_history_id']);
 */
class CreatePartitions implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION create_partitions(
    table_name TEXT,
    table_short_name TEXT,
    interval_name TEXT = '1 month',
    interval_measure TEXT = 'month',
    partition_name_format TEXT = 'YYYY_MM',
    interval_to_forward INT = 1,
    interval_to_back INT = 0,
    unique_columns TEXT[] = ARRAY[]::TEXT[]
) RETURNS SETOF TEXT AS
$func$
DECLARE
    _partition_date TEXT;
    _partition TEXT;
    _index_prefix TEXT;
    _next_date date;
    _start_date date;
    _end_date date;
BEGIN
    FOR i IN interval_to_back..interval_to_forward
        LOOP
            _next_date := now() + i * (interval_name)::INTERVAL;
            _partition_date := to_char(_next_date, partition_name_format);
            _partition := table_short_name || '_' || _partition_date;
            _index_prefix := table_short_name || '_' || _partition_date;
            _start_date := date_trunc(interval_measure, _next_date);
            _end_date := date_trunc(interval_measure, _next_date) + (interval_name)::INTERVAL;

            IF NOT EXISTS(SELECT relname FROM pg_class WHERE relname=_partition) THEN
                EXECUTE 'CREATE TABLE ' || _partition || ' partition OF ' || table_name || ' FOR VALUES FROM (''' || _start_date || ''') TO (''' || _end_date || ''');';
                EXECUTE 'ALTER TABLE ' || _partition || ' add primary key(id);';

                IF cardinality(unique_columns) > 0 THEN
                    FOR col_num IN array_lower(unique_columns, 1) .. array_upper(unique_columns, 1)
                        LOOP
                            EXECUTE 'CREATE UNIQUE INDEX uniq_' || _index_prefix || '_' || unique_columns[col_num] || ' ON ' || _partition || '(' || unique_columns[col_num] || ')';
                            RAISE NOTICE 'A UNIQUE INDEX has been created %', unique_columns[col_num];
                        END LOOP;
                END IF;

                RAISE NOTICE 'A partition has been created %', _partition;
            ELSE
                RAISE NOTICE 'A partition already exists %', _partition;
            END IF;

            RETURN NEXT _partition;
        END LOOP;
    RETURN;
END;
$func$ LANGUAGE plpgsql;
SQL;
    }
}