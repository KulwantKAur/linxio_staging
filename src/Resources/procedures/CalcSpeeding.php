<?php


namespace App\Resources\procedures;


class CalcSpeeding implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION calc_speeding (
	exc_speed_count INT,
	total_distance DECIMAL,
	events_total_distance DECIMAL
) RETURNS DECIMAL AS $func$
DECLARE
_range DECIMAL;
_multiplier INT;
_result DECIMAL;
BEGIN
    IF total_distance = 0 THEN
		RETURN 100;
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
	
    _result := 100 - ((events_total_distance / total_distance) * _multiplier);
    
    IF _result < 0 THEN
        RETURN 0;
    ELSIF _result > 100 THEN
        RETURN 100;
    ELSE
        RETURN _result;
    END IF;
END $func$ LANGUAGE plpgsql;        
SQL;
    }
}