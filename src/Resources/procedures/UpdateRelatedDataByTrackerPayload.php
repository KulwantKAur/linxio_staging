<?php

namespace App\Resources\procedures;

class UpdateRelatedDataByTrackerPayload implements InsertProcedureInterface
{
    public static function up(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION update_related_data_by_tracker_payload() RETURNS trigger AS
$res$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        UPDATE tracker_history SET tracker_payload_id = NULL WHERE tracker_payload_id = OLD.id;
        UPDATE tracker_history_dtc_vin SET tracker_payload_id = NULL WHERE tracker_payload_id = OLD.id;
        UPDATE tracker_sensor SET tracker_payload_id = NULL WHERE tracker_payload_id = OLD.id;
        UPDATE traccar_event_history SET payload_id = NULL WHERE payload_id = OLD.id;
        UPDATE tracker_history_sensor SET tracker_payload_id = NULL WHERE tracker_payload_id = OLD.id;
        UPDATE driving_behavior SET tracker_payload_id = NULL WHERE tracker_payload_id = OLD.id;
    ELSIF (TG_OP = 'UPDATE') THEN
        UPDATE tracker_history SET tracker_payload_id = NEW.id WHERE tracker_payload_id = OLD.id;
        UPDATE tracker_history_dtc_vin SET tracker_payload_id = NEW.id WHERE tracker_payload_id = OLD.id;
        UPDATE tracker_sensor SET tracker_payload_id = NEW.id WHERE tracker_payload_id = OLD.id;
        UPDATE traccar_event_history SET payload_id = NEW.id WHERE payload_id = OLD.id;
        UPDATE tracker_history_sensor SET tracker_payload_id = NEW.id WHERE tracker_payload_id = OLD.id;
        UPDATE driving_behavior SET tracker_payload_id = NEW.id WHERE tracker_payload_id = OLD.id;
    END IF;

    RETURN NEW;
END;
$res$ LANGUAGE plpgsql;
SQL;
    }
}