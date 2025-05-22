<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Notification\Event;
use App\Entity\Notification\ScopeType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200706143725 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $upCondition = [
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::TRACKER_VOLTAGE,
                ],
                "old_type" => 'tracker',
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
        ];

        $this->updateScopesForNotifications($upCondition);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $downCondition = [
            [
                "new_type" => 'tracker',
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::TRACKER_VOLTAGE,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
        ];

        $this->updateScopesForNotifications($downCondition);
    }

    private function updateScopesForNotifications(array $conditions)
    {
        foreach ($conditions as $condition) {
            $condition['search_event_name'] =  "'". implode("','", $condition['search_event_name']) . "'";

            $this->addSql(
                'UPDATE notification_scopes
            SET type_id = (SELECT nt_sct.id AS id FROM notification_scope_type nt_sct
                WHERE nt_sct.type = \''. $condition['new_type'] .'\' AND nt_sct.sub_type = \''. $condition['new_sub_type'] .'\')
            FROM (
                SELECT
                    nt.id AS ntf_id
                    FROM notification nt
                    LEFT JOIN notification_event nt_e ON nt.event_id = nt_e.id
                WHERE nt_e.name IN ('. $condition['search_event_name'] . ')
                ) AS notification
               WHERE notification_id = notification.ntf_id
                 AND type_id = (SELECT nt_sct.id FROM notification_scope_type nt_sct WHERE nt_sct.type = \''. $condition['old_type'] .'\' AND nt_sct.sub_type = \''. $condition['old_sub_type'] .'\')'
            );

            $this->addSql(
                'UPDATE notification_events2scopes_types
            SET scope_type_id = (SELECT nt_sct.id AS id FROM notification_scope_type nt_sct
                WHERE nt_sct.type = \''. $condition['new_type'] .'\' AND nt_sct.sub_type = \''. $condition['new_sub_type'] .'\')
            FROM (
                SELECT
                    nt_e.id AS nte_id
                    FROM notification_event nt_e
                    WHERE nt_e.name IN ('. $condition['search_event_name'] . ')
                ) AS ntf_event
               WHERE event_id = ntf_event.nte_id
                 AND scope_type_id = (SELECT nt_sct.id FROM notification_scope_type nt_sct WHERE nt_sct.type = \''. $condition['old_type'] .'\' AND nt_sct.sub_type = \''. $condition['old_sub_type'] .'\')'
            );
        }
    }
}
