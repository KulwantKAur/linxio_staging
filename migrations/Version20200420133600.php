<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Notification\Event;
use App\Entity\Notification\ScopeType;
use App\Entity\Reminder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200420133600 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $upCondition = [
            [
                "new_type" => ScopeType::REMINDER,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
            [
                "new_type" => ScopeType::REMINDER,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::REMINDER,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::REMINDER,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
            ],
            [
                "new_type" => ScopeType::DOCUMENT_RECORD,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
            [
                "new_type" => ScopeType::DOCUMENT_RECORD,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::DOCUMENT_RECORD,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::DOCUMENT_RECORD,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
            ],
            [
                "new_type" => ScopeType::DOCUMENT,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
            [
                "new_type" => ScopeType::DOCUMENT,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::DOCUMENT,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::DOCUMENT,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::VEHICLE,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
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
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::REMINDER,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,


            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::REMINDER,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::REMINDER,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::SERVICE_REMINDER_SOON,
                    Event::SERVICE_REMINDER_EXPIRED,
                    Event::SERVICE_REMINDER_DONE,
                    Event::SERVICE_REMINDER_DELETED,
                ],
                "old_type" => ScopeType::REMINDER,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::DOCUMENT_RECORD,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::DOCUMENT_RECORD,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::DOCUMENT_RECORD,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::DOCUMENT_EXPIRE_SOON,
                    Event::DOCUMENT_EXPIRED,
                ],
                "old_type" => ScopeType::DOCUMENT_RECORD,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_ANY,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::DOCUMENT,
                "old_sub_type" => ScopeType::SUBTYPE_ANY,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_VEHICLE,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::DOCUMENT,
                "old_sub_type" => ScopeType::SUBTYPE_VEHICLE,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_DEPOT,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::DOCUMENT,
                "old_sub_type" => ScopeType::SUBTYPE_DEPOT,
            ],
            [
                "new_type" => ScopeType::VEHICLE,
                "new_sub_type" => ScopeType::SUBTYPE_GROUP,
                "search_event_name" => [
                    Event::DOCUMENT_DELETED,
                ],
                "old_type" => ScopeType::DOCUMENT,
                "old_sub_type" =>  ScopeType::SUBTYPE_GROUP,
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
        }
    }
}
