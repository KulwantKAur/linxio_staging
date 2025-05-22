<?php

namespace App\Resources\procedures;

// @todo not using
class SeatBelt implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION seat_belt (
	exc_speed_count INT,
	total_distance DECIMAL,
	events_total_distance DECIMAL
) RETURNS DECIMAL AS $func$
DECLARE
_range DECIMAL;
_multiplier INT;
BEGIN
    IF total_distance = 0 THEN
		RETURN 0;
	END IF;
	
	_range := (events_total_distance / total_distance) * 100;
	CASE 
		WHEN _range = 0 THEN _multiplier:=0;
		WHEN _range < 0.3 THEN _multiplier:=6;
		WHEN 0.3 <= _range AND _range < 0.7 THEN _multiplier:=7;
		WHEN 0.7 <= _range AND _range < 1 THEN _multiplier:=8;
		WHEN 1 <= _range AND _range < 1.3 THEN _multiplier:=9;
		WHEN 1.3 <= _range AND _range < 1.7 THEN _multiplier:=10;
		WHEN 1.7 <= _range AND _range < 2 THEN _multiplier:=11;
		WHEN 2 <= _range AND _range < 2.3 THEN _multiplier:=12;
		WHEN 2.3 <= _range AND _range < 2.7 THEN _multiplier:=13;
		WHEN 2.7 <= _range AND _range < 3 THEN _multiplier:=14;
		WHEN 3 <= _range AND _range < 3.3 THEN _multiplier:=15;
		WHEN 3.3 <= _range AND _range < 3.7 THEN _multiplier:=16;
		WHEN 3.7 <= _range AND _range < 4 THEN _multiplier:=17;
		WHEN 4 <= _range AND _range < 4.3 THEN _multiplier:=18;
		WHEN 4.3 <= _range AND _range < 4.7 THEN _multiplier:=19;
		WHEN 4.7 <= _range AND _range < 5 THEN _multiplier:=20;
		ELSE _multiplier:=20;
	END CASE;

	RETURN (100 - (exc_speed_count * 1000) / total_distance) * 0.3 + (100 - _range * _multiplier) * 0.7; 
END $func$ LANGUAGE plpgsql;        
SQL;
    }
}
