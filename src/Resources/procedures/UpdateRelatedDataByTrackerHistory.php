<?php

namespace App\Resources\procedures;

class UpdateRelatedDataByTrackerHistory implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION update_related_data_by_tracker_history() RETURNS trigger AS
$res$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        DELETE FROM tracker_history_temp_part WHERE tracker_history_id = OLD.id;
        IF NOT FOUND THEN RETURN NULL;
        END IF;
    END IF;

    RETURN NEW;
END;
$res$ LANGUAGE plpgsql;
SQL;
    }
}