<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210325173519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->updateSensorLightData();
        $this->updateSensorLightNullableData();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }

    private function updateSensorLightData()
    {
        $this->addSql('UPDATE tracker_history_sensor
            SET data = (data)::jsonb || CONCAT(\'{"ambientLightStatus":\', ths_sub_records.ambient_light_value::TEXT, \'}\')::jsonb
            FROM (SELECT ths_sub.id AS ths_sub_id, ths_sub.data -> \'ambientLightData\' ->> \'value\' AS ambient_light_value
                  FROM tracker_history_sensor ths_sub
                  WHERE ths_sub.data ->> \'ambientLightData\' IS NOT NULL
                    AND ths_sub.data ->> \'ambientLightStatus\' IS NULL) AS ths_sub_records
            WHERE tracker_history_sensor.id = ths_sub_records.ths_sub_id
                AND tracker_history_sensor.data ->> \'ambientLightData\' IS NOT NULL
                AND tracker_history_sensor.data ->> \'ambientLightStatus\' IS NULL;
        ');
    }

    private function updateSensorLightNullableData()
    {
        $this->addSql('UPDATE tracker_history_sensor
            SET data = (data)::jsonb || \'{"ambientLightStatus":null}\'::jsonb
            FROM (SELECT ths_sub.id AS ths_sub_id
                  FROM tracker_history_sensor ths_sub
                  WHERE ths_sub.data -> \'ambientLightData\' IS NOT NULL
                        AND ths_sub.data ->> \'ambientLightData\' IS NULL) AS ths_sub_records
            WHERE tracker_history_sensor.id = ths_sub_records.ths_sub_id;
        ');
    }
}
