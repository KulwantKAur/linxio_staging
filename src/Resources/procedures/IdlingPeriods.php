<?php


namespace App\Resources\procedures;

// @todo not using
class IdlingPeriods implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_idling_periods (
    select_date_start TIMESTAMP,
    select_date_end TIMESTAMP,
    vehicle_ids INT []
) RETURNS TABLE (
    _ids BIGINT [], 
    vehicle INT,
    start_period TIMESTAMP,
    end_period TIMESTAMP,
	lng NUMERIC,
	lat NUMERIC
) AS $func$
DECLARE
    _db driving_behavior;
    _previus_condition_true BOOLEAN;
    _first_ts TIMESTAMP;
    _last_ts TIMESTAMP;
    _prev_vehicle_id INT;
    _curr_vehicle_id INT;
    _is_returned BOOLEAN;
	_lng NUMERIC;
	_lat NUMERIC;
BEGIN
    _previus_condition_true := FALSE;
    _is_returned := FALSE;
    FOR _db IN SELECT * FROM driving_behavior WHERE vehicle_id=ANY(vehicle_ids) AND ts BETWEEN select_date_start AND select_date_end ORDER BY vehicle_id DESC, ts ASC
        LOOP
            _is_returned := FALSE;
            _prev_vehicle_id := _curr_vehicle_id;
            _curr_vehicle_id := _db.vehicle_id;

            IF _prev_vehicle_id = _curr_vehicle_id THEN
                IF _db.ignition = 1 AND _db.speed = 0 THEN
                    IF _previus_condition_true = FALSE THEN
                        _ids := ARRAY[_db.id];
                        _first_ts := _db.ts;
                        _last_ts := _db.ts;
                        _lng := _db.lng;
                        _lat := _db.lat;
                    ELSE
                        _ids := _ids || _db.id;
                        _last_ts := _db.ts;
                    END IF;
                    _previus_condition_true := TRUE;
                ELSE
                    IF _previus_condition_true = TRUE THEN
                        _is_returned := TRUE;
                        RETURN QUERY SELECT _ids, _db.vehicle_id as vehicle , _first_ts as start_period, _last_ts as end_period, _lng as lng, _lat as lat;
                    END IF;
                    _previus_condition_true := FALSE;
                END IF;
            ELSE
                IF _previus_condition_true = TRUE THEN
                    _is_returned := TRUE;
                    RETURN QUERY SELECT _ids, _prev_vehicle_id as vehicle, _first_ts as start_period, _last_ts as end_period, _lng as lng, _lat as lat;
                END IF;

                _ids := ARRAY[_db.id];
                _first_ts := _db.ts;
                _last_ts := _db.ts;
                _lng := _db.lng;
                _lat := _db.lat;
                IF _db.ignition = 1 AND _db.speed = 0 THEN
                    _previus_condition_true := TRUE;
                    _is_returned := FALSE;
                ELSE
                    _previus_condition_true := FALSE;
                END IF;
            END IF;
        END LOOP;

    IF _previus_condition_true = TRUE AND _is_returned = FALSE THEN
        RETURN QUERY SELECT _ids, _curr_vehicle_id as vehicle, _first_ts as start_period, _last_ts as end_period, _lng as lng, _lat as lat;
    END IF;
END $func$ LANGUAGE plpgsql;
SQL;
    }
}