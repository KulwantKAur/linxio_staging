<?php

namespace App\Resources\procedures;

/**
 * @example SELECT delete_old_tracker_payload_records();
 */
class DeleteOldTrackerPayloads implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION delete_old_tracker_payload_records(_loops INT = 100, _limit INT = 100, _min_old_id INT = 1)
    RETURNS void AS
$func$
DECLARE
    _min_id INT;
    _current_id INT;
    _current_seq INT;
    _current_datetime TIMESTAMP;
BEGIN
    SELECT last_value FROM tracker_payload_id_seq INTO _current_seq;

    IF _current_seq > 2100000000  THEN
        RAISE INFO 'Sequence tracker_payload_id_seq will be reset to 1';
        ALTER SEQUENCE tracker_payload_id_seq RESTART WITH 1;
    END IF;
        
    SELECT MIN(id) FROM tracker_payload WHERE id > _min_old_id INTO _min_id;
    _current_id := _min_id + (_loops * _limit);
    SELECT created_at FROM tracker_payload WHERE id = _current_id INTO _current_datetime;

    IF NOT FOUND THEN
        SELECT id FROM tracker_payload WHERE id > _current_id ORDER BY id LIMIT 1 INTO _current_id;
        SELECT created_at FROM tracker_payload WHERE id = _current_id INTO _current_datetime;
    END IF;
    
    IF _current_datetime < (NOW() - INTERVAL '1' MONTH) THEN
        FOR i IN 1.._loops
            LOOP
                DELETE FROM tracker_payload WHERE id IN (SELECT id FROM tracker_payload WHERE id <= _current_id AND id >= _min_old_id LIMIT _limit);
            END LOOP;
    END IF;
END
$func$ LANGUAGE plpgsql;
SQL;
    }
}