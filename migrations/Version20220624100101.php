<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Resources\procedures\UpdateRelatedDataByTrackerPayload;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220624100101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update related data when some changes are in tracker_payload';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT fk_e08b378810db296a');
        $this->addSql('ALTER TABLE tracker_history_sensor DROP CONSTRAINT fk_f02ce63e10db296a');
        $this->addSql('ALTER TABLE traccar_event_history DROP CONSTRAINT fk_a9502bbd1664b27');
        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT fk_8b4edec410db296a');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin DROP CONSTRAINT fk_7bdf66b310db296a');
        $this->addSql('ALTER TABLE tracker_history DROP CONSTRAINT fk_70e50da710db296a');
        $this->addSql(UpdateRelatedDataByTrackerPayload::up());
        $this->addSql('CREATE TRIGGER update_related_data_by_tracker_payload_trigger AFTER DELETE OR UPDATE OF id ON tracker_payload FOR EACH ROW EXECUTE PROCEDURE update_related_data_by_tracker_payload()');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS update_related_data_by_tracker_payload_trigger ON tracker_payload');
        $this->addSql('ALTER TABLE tracker_history ADD CONSTRAINT fk_70e50da710db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_dtc_vin ADD CONSTRAINT fk_7bdf66b310db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT fk_8b4edec410db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traccar_event_history ADD CONSTRAINT fk_a9502bbd1664b27 FOREIGN KEY (payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_sensor ADD CONSTRAINT fk_f02ce63e10db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT fk_e08b378810db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
