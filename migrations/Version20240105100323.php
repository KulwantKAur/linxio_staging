<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240105100323 extends AbstractMigration
{
    private function copyDataToTrackerHistoryTempPartTrigger()
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION copy_data_to_tracker_history_temp_part() RETURNS trigger AS $res$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        DELETE FROM tracker_history_temp_part WHERE id = OLD.id;
    ELSIF (TG_OP = 'UPDATE') THEN
        UPDATE tracker_history_temp_part
        SET is_calculated = NEW.is_calculated, is_calculated_idling = NEW.is_calculated_idling, is_calculated_speeding = NEW.is_calculated_speeding WHERE id = NEW.id;
--         IF NOT FOUND THEN RETURN NULL;
--         END IF;
    ELSIF (TG_OP = 'INSERT') THEN
        INSERT INTO tracker_history_temp_part SELECT NEW.*;
    END IF;

    RETURN NEW;
END;
$res$ LANGUAGE plpgsql;
SQL;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql($this->copyDataToTrackerHistoryTempPartTrigger());
    }

    public function down(Schema $schema): void
    {}
}
