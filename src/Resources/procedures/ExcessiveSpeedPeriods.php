<?php


namespace App\Resources\procedures;

// @todo not using
class ExcessiveSpeedPeriods implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION get_excessive_speed_periods (
    excessive_speed_map JSON,
    select_date_start TIMESTAMP,
    select_date_end TIMESTAMP,
    vehicle_ids INT []
) RETURNS TABLE (
    _ids BIGINT [], 
    vehicle INT,
    start_period TIMESTAMP,
    end_period TIMESTAMP,
    avg_speed DECIMAL,
    distance DECIMAL,
	coordinates JSON
) AS $func$
DECLARE
    _db driving_behavior;
    _previus_condition_true BOOLEAN;
    _first_ts TIMESTAMP;
    _last_ts TIMESTAMP;
    _prev_vehicle_id INT;
    _curr_vehicle_id INT;
    _is_returned BOOLEAN;
    _speeds DECIMAL[];
    _first_odometer DECIMAL;
    _last_odometer DECIMAL;
    _excessive_speed DECIMAL;
	_coordinates JSON[];

BEGIN
    _previus_condition_true := FALSE;
    FOR _db IN SELECT * FROM driving_behavior WHERE vehicle_id=ANY(vehicle_ids) AND ts BETWEEN select_date_start AND select_date_end ORDER BY vehicle_id DESC, ts ASC
        LOOP
            _is_returned := FALSE;
            _prev_vehicle_id := _curr_vehicle_id;
            _curr_vehicle_id := _db.vehicle_id;

            _excessive_speed := 0;

            IF excessive_speed_map->>(_db.vehicle_id::VARCHAR) IS NOT NULL THEN
                _excessive_speed := (excessive_speed_map->>(_db.vehicle_id::VARCHAR))::DECIMAL;
            END IF;

            IF _prev_vehicle_id = _curr_vehicle_id THEN

                IF _db.speed > _excessive_speed THEN
                    IF _previus_condition_true = FALSE THEN
                        _ids := ARRAY[_db.id];
						_coordinates := ARRAY[('{' || '"lat":"' || _db.lat || '","lng":"' || _db.lng|| '"}')::JSON];
                        _speeds := ARRAY[_db.speed::DECIMAL]; 
                        _first_ts := _db.ts;
                        _last_ts := _db.ts;
                        _first_odometer := _db.odometer::DECIMAL;
                        _last_odometer := _db.odometer::DECIMAL;
                    ELSE
                        _ids := _ids || _db.id;
						_coordinates :=  _coordinates || ('{' || '"lat":"' || _db.lat || '","lng":"' || _db.lng|| '"}')::JSON;
                        _speeds := _speeds || _db.speed::DECIMAL;
                        _last_ts := _db.ts;
                        _last_odometer := _db.odometer;
                    END IF;

                    _previus_condition_true := TRUE;
                ELSE
                    IF _previus_condition_true = TRUE THEN
                        _is_returned := TRUE;
                        RETURN QUERY SELECT _ids, _db.vehicle_id as vehicle, _first_ts as start_period, _last_ts as end_period, AVG(n)::DECIMAL as avg_speed, (_last_odometer - _first_odometer) as distance , array_to_json(_coordinates) as coordinates FROM UNNEST(_speeds::DECIMAL[]) as _t(n);
                    END IF;
                    _previus_condition_true := FALSE;
                END IF;
            ELSE
                IF _previus_condition_true = TRUE THEN
                    _is_returned := TRUE;
                    RETURN QUERY SELECT _ids, _prev_vehicle_id as vehicle, _first_ts as start_period, _last_ts as end_period, AVG(n)::DECIMAL as avg_speed, (_last_odometer - _first_odometer) as distance, array_to_json(_coordinates) as coordinates FROM UNNEST(_speeds::DECIMAL[]) as _t(n);
                END IF;

                _ids := ARRAY[_db.id];
				_coordinates := ARRAY[('{' || '"lat":"' || _db.lat || '","lng":"' || _db.lng|| '"}')::JSON];
                _speeds := ARRAY[_db.speed::DECIMAL];
                _first_ts := _db.ts;
                _last_ts := _db.ts;
                _first_odometer := _db.odometer::DECIMAL;
                _last_odometer := _db.odometer::DECIMAL;

                IF _db.speed > _excessive_speed THEN
                    _previus_condition_true := TRUE;
                    _is_returned := FALSE;
                ELSE
                    _previus_condition_true := FALSE;
                END IF;
            END IF;
        END LOOP;

    IF _previus_condition_true = TRUE AND _is_returned = FALSE THEN
        RETURN QUERY SELECT _ids, _curr_vehicle_id as vehicle, _first_ts as start_period, _last_ts as end_period, AVG(n)::DECIMAL as speeds_avg, (_last_odometer - _first_odometer) as distance, array_to_json(_coordinates) as coordinates FROM UNNEST(_speeds::DECIMAL[]) as _t(n);
    END IF;
END $func$ LANGUAGE plpgsql;
SQL;
    }
}