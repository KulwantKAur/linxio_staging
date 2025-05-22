<?php
namespace App\Resources\procedures;

class CalcEventScore implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION calc_event_score (
	total_distance DECIMAL,
	event_count NUMERIC
) RETURNS DECIMAL AS $func$
DECLARE
_result DECIMAL;
BEGIN
	IF total_distance = 0 THEN
		RETURN 100;
	ELSE
        _result := 100 - ((event_count * 1000) / (total_distance / 1000));
        
        IF _result < 0 THEN
            RETURN 0;
        ELSIF _result > 100 THEN
            RETURN 100;
        ELSE
            RETURN _result;
        END IF;	    
	END IF;	
END $func$ LANGUAGE plpgsql;
SQL;
    }
}