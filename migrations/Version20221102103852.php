<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221102103852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $eventLogInsertTriggerFunction = <<<'SQL'
CREATE OR REPLACE FUNCTION event_log_insert_trigger_function()
    RETURNS TRIGGER AS
$$
DECLARE
    current_date_part      DATE;
    current_date_part_text TEXT;
    partition_table_name   TEXT;
    first_day_of_month     DATE;
    last_day_of_month      DATE;
BEGIN
    current_date_part := CAST(DATE_TRUNC('month', NEW.event_date) AS DATE);
    current_date_part_text := REGEXP_REPLACE(current_date_part::TEXT, '-', '_', 'g');
    partition_table_name := FORMAT('event_log_%s', current_date_part_text::TEXT);
    IF (TO_REGCLASS(partition_table_name::TEXT) ISNULL) THEN
        first_day_of_month := current_date_part;
        last_day_of_month := current_date_part + '1 month'::INTERVAL;
        EXECUTE FORMAT(
                'CREATE TABLE %I ('
                    '  CHECK (event_date >= DATE %L AND event_date < DATE %L)'
                    ') INHERITS (event_log);'
            , partition_table_name, first_day_of_month, last_day_of_month);
        EXECUTE FORMAT(
                'ALTER TABLE ONLY %1$I ADD CONSTRAINT %1$s__pkey PRIMARY KEY (id);'
            , partition_table_name);
        EXECUTE FORMAT(
                'CREATE INDEX IF NOT EXISTS %1$s_event_date_index ON %1$I (event_date);'
            , partition_table_name);
        EXECUTE FORMAT(
                'CREATE INDEX IF NOT EXISTS %1$s_event_id_index ON %1$I (event_id);'
            , partition_table_name);
        EXECUTE FORMAT(
                'CREATE INDEX IF NOT EXISTS %1$s_entity_team_id_index ON %1$I (entity_team_id);'
            , partition_table_name);
        EXECUTE FORMAT(
                'CREATE INDEX IF NOT EXISTS %1$s_entity_id_index ON %1$I (entity_id, event_id);'
            , partition_table_name);
    END IF;
    EXECUTE FORMAT('INSERT INTO %I VALUES ($1.*)', partition_table_name) USING NEW;

    RETURN NULL;
END;
$$
    LANGUAGE plpgsql

SQL;

        $eventLogInsertTrigger = <<< 'SQL'
CREATE TRIGGER insert_event_log_trigger
    BEFORE INSERT
    ON event_log
    FOR EACH ROW
EXECUTE FUNCTION event_log_insert_trigger_function();
SQL;

        $this->addSql($eventLogInsertTriggerFunction);
        $this->addSql($eventLogInsertTrigger);
        $this->addSql("alter table acknowledge drop constraint fk_b8037dd4d8fe2ad4");
        $this->addSql("alter table notification_message drop constraint fk_a3a3bac8d8fe2ad4");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP FUNCTION if exists event_log_insert_trigger_function');
        $this->addSql('DROP TRIGGER IF EXISTS insert_event_log_trigger ON event_log');
    }
}
