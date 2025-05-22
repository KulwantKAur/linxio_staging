<?php

namespace App\Fixtures\Notification\Alert;

use App\Entity\Notification\Alert\AlertSubType;
use App\Entity\Notification\Alert\AlertType;
use App\Entity\Notification\Alert\AlertSetting;
use App\Entity\Notification\Event;
use App\Entity\Plan;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Notification\InitEventsFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitAlertSettingFixtures extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            InitAlertTypeFixture::class,
            InitAlertSubTypeFixture::class,
            InitEventsFixture::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        $dataGenerate = array_merge(
            $this->generatedDataForAdminTeam(Team::TEAM_ADMIN),
            $this->generatedDataForClientTeam(),
            $this->generatedDataForAdminTeam(Team::TEAM_RESELLER)
        );
        $sort = 0;

        $alertSettingIds = [];
        foreach ($dataGenerate as $key => $data) {
            /** @var Event $refEvent */
            $refEvent = $this->getReference(implode('_', $data['event']));

            /** @var AlertType $refAlertType */
            $refAlertType = $this->getReference('alerts_type_' . $data['alertType']);

            /** @var AlertSubType $refAlertSubType */
            $refAlertSubType = $this->getReference('alerts_subtype_' . $data['alertSubtype']);

            if (isset($data['plans'])) {
                foreach ($data['plans'] as $planName) {
                    $plan = $manager->getRepository(Plan::class)->findOneBy(['name' => $planName]);

                    $alertSetting = $this->createAlertSetting(
                        $manager,
                        $data['team'],
                        $refEvent,
                        $refAlertType,
                        $refAlertSubType,
                        $sort,
                        $plan
                    );
                    $alertSettingIds[] = $alertSetting->getId();
                }
            } else {
                $alertSetting = $this->createAlertSetting(
                    $manager,
                    $data['team'],
                    $refEvent,
                    $refAlertType,
                    $refAlertSubType,
                    $sort,
                    null
                );
                $alertSettingIds[] = $alertSetting->getId();
            }

            ++$sort;
        }
        $manager->getRepository(AlertSetting::class)->removeClientAlertSettingById($alertSettingIds);
        $manager->flush();
    }

    private function createAlertSetting($manager, $team, $refEvent, $refAlertType, $refAlertSubType, $sort, $plan)
    {
        $alertSetting = $manager->getRepository(AlertSetting::class)->findOneBy([
            'team' => $team,
            'event' => $refEvent,
            'alertType' => $refAlertType,
            'alertSubtype' => $refAlertSubType,
            'plan' => $plan
        ]);

        if (!$alertSetting) {
            $alertSetting = (new AlertSetting())
                ->setTeam($team)
                ->setEvent($refEvent)
                ->setAlertType($refAlertType)
                ->setAlertSubtype($refAlertSubType)
                ->setSort($sort)
                ->setPlan($plan);

            $manager->persist($alertSetting);
        } else {
            $alertSetting->setSort($sort);
        }

        return $alertSetting;
    }

    /**
     * @return array
     */
    public function generatedDataForClientTeam()
    {
        $team = Team::TEAM_CLIENT;

        return [
            /* Vehicle Activities */
            [
                'team' => $team,
                'event' => [Event::VEHICLE_DRIVING_WITHOUT_DRIVER, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_OVERSPEEDING, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_LONG_DRIVING, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_LONG_STANDING, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_MOVING, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_EXCESSING_IDLING, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_TOWING_EVENT, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::TRACKER_VOLTAGE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::TRACKER_BATTERY_PERCENTAGE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            /* Vehicle Documents */
            [
                'team' => $team,
                'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DOCUMENT_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            /* Digital Forms */
            [
                'team' => $team,
                'event' => [Event::DIGITAL_FORM_WITH_FAIL, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DIGITAL_FORMS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DIGITAL_FORMS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            /* Driver Activities */
            [
                'team' => $team,
                'event' => [Event::VEHICLE_REASSIGNED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_ACTIVITIES,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DRIVER_ROUTE_UNDEFINED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_ACTIVITIES,
                'plans' => [Plan::PLAN_PLUS]
            ],
            /* Sensor Data */
            [
                'team' => $team,
                'event' => [Event::SENSOR_TEMPERATURE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SENSOR_HUMIDITY, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SENSOR_LIGHT, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SENSOR_BATTERY_LEVEL, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SENSOR_STATUS, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SENSOR_IO_STATUS, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::PANIC_BUTTON, Event::TYPE_USER],
                'alertType' => AlertType::DEVICE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SENSOR_DATA,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::TRACKER_JAMMER_STARTED_ALARM, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::TRACKER_ACCIDENT_HAPPENED_ALARM, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            /* Driver Documents */
            [
                'team' => $team,
                'event' => [Event::DRIVER_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
                'alertType' => AlertType::DRIVER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DRIVER_DOCUMENT_EXPIRED, Event::TYPE_USER],
                'alertType' => AlertType::DRIVER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DRIVER_DOCUMENT_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::DRIVER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::DRIVER_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
                'alertType' => AlertType::DRIVER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DRIVER_DOCUMENTS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            /* Vehicle Status */
            [
                'team' => $team,
                'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_STATUS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_STATUS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_STATUS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_STATUS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_STATUS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            /* Areas */
            [
                'team' => $team,
                'event' => [Event::VEHICLE_GEOFENCE_ENTER, Event::TYPE_USER],
                'alertType' => AlertType::TERRITORY_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_AREAS,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_GEOFENCE_LEAVE, Event::TYPE_USER],
                'alertType' => AlertType::TERRITORY_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_AREAS,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, Event::TYPE_USER],
                'alertType' => AlertType::TERRITORY_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_AREAS,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::EXCEEDING_SPEED_LIMIT, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            /* Service Reminders */
            [
                'team' => $team,
                'event' => [Event::SERVICE_REMINDER_SOON, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SERVICE_REMINDER_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SERVICE_RECORD_ADDED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SERVICE_REMINDER_DONE, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::SERVICE_REPAIR_ADDED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_SERVICE_REMINDERS,
                'plans' => [Plan::PLAN_PLUS]
            ],
            /* Vehicle Data */
            [
                'team' => $team,
                'event' => [Event::VEHICLE_CHANGED_REGNO, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DATA,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::VEHICLE_CHANGED_MODEL, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DATA,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::ODOMETER_CORRECTED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_VEHICLE_DATA,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            /* User Accounts */
            [
                'team' => $team,
                'event' => [Event::USER_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::USER_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::USER_CHANGED_NAME, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
//            [
//                'team' => $team,
//                'event' => [Event::ASSET_DOCUMENT_DELETED, Event::TYPE_USER],
//                'alertType' => AlertType::VEHICLE_ALERTS,
//                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_DOCUMENTS,
//            ],
//            [
//                'team' => $team,
//                'event' => [Event::ASSET_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
//                'alertType' => AlertType::VEHICLE_ALERTS,
//                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_DOCUMENTS,
//            ],
//            [
//                'team' => $team,
//                'event' => [Event::ASSET_DOCUMENT_EXPIRED, Event::TYPE_USER],
//                'alertType' => AlertType::VEHICLE_ALERTS,
//                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_DOCUMENTS,
//            ],
//            [
//                'team' => $team,
//                'event' => [Event::ASSET_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
//                'alertType' => AlertType::VEHICLE_ALERTS,
//                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_DOCUMENTS,
//            ],
            [
                'team' => $team,
                'event' => [Event::ASSET_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::ASSET_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::ASSET_MISSED, Event::TYPE_USER],
                'alertType' => AlertType::VEHICLE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ASSET_ACTIVITIES,
                'plans' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::PAYMENT_SUCCESSFUL, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::PAYMENT_FAILED, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE_BLOCKED, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
            [
                'team' => $team,
                'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_USER_ACCOUNTS,
                'plans' => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
            ],
        ];
    }

    /**
     * @param null $team
     * @return array[]
     */
    public function generatedDataForAdminTeam($team = null): array
    {
        $team = $team ?? Team::TEAM_ADMIN;

        return [
            /* User Activities */
            [
                'team' => $team,
                'event' => [Event::ADMIN_USER_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::ADMIN_USER_BLOCKED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::ADMIN_USER_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::ADMIN_USER_PWD_RESET, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::ADMIN_USER_CHANGED_NAME, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            /* Admin activities */
            [
                'team' => $team,
                'event' => [Event::LOGIN_AS_USER, Event::TYPE_USER],
                'alertType' => AlertType::SYSTEM_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_ACTIVITIES,
            ],
            /* Client accounts */
            [
                'team' => $team,
                'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_CLIENTS_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_CLIENTS_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_CLIENTS_ACCOUNTS,
            ],
            /* Device status */
            [
                'team' => $team,
                'event' => [Event::DEVICE_UNKNOWN_DETECTED, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_IN_STOCK, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_OFFLINE, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_UNAVAILABLE, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_DELETED, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_REPLACED, Event::TYPE_USER],
                'alertType' => AlertType::TECHNICAL_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            /* Stripe integration */
            [
                'team' => $team,
                'event' => [Event::STRIPE_INTEGRATION_ERROR, Event::TYPE_USER],
                'alertType' => AlertType::STRIPE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_STRIPE_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::STRIPE_PAYMENT_FAILED, Event::TYPE_USER],
                'alertType' => AlertType::STRIPE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_STRIPE_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::STRIPE_PAYMENT_SUCCESSFUL, Event::TYPE_USER],
                'alertType' => AlertType::STRIPE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_STRIPE_INTEGRATION,
            ],
            /* Xero integration */
            [
                'team' => $team,
                'event' => [Event::XERO_INTEGRATION_ERROR, Event::TYPE_USER],
                'alertType' => AlertType::XERO_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_XERO_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::XERO_INVOICE_CREATION_ERROR, Event::TYPE_USER],
                'alertType' => AlertType::XERO_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_XERO_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::XERO_INVOICE_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::XERO_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_XERO_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::XERO_PAYMENT_CREATION_ERROR, Event::TYPE_USER],
                'alertType' => AlertType::XERO_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_XERO_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::XERO_PAYMENT_CREATED, Event::TYPE_USER],
                'alertType' => AlertType::XERO_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_XERO_INTEGRATION,
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_CREATED_ADMIN, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS_ADMIN,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO_ADMIN,
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE_ADMIN, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS_ADMIN,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO_ADMIN,
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS_ADMIN,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO_ADMIN,
            ],
            [
                'team' => $team,
                'event' => [Event::INVOICE_OVERDUE_BLOCKED_ADMIN, Event::TYPE_USER],
                'alertType' => AlertType::BILLING_ALERTS_ADMIN,
                'alertSubtype' => AlertSubType::SUBTYPE_BILLING_INFO_ADMIN,
            ],
            [
                'team' => $team,
                'event' => [Event::DEVICE_CONTRACT_EXPIRED, Event::TYPE_USER],
                'alertType' => AlertType::DEVICE_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_DEVICE_STATUS,
            ],
            [
                'team' => $team,
                'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
            ],
            [
                'team' => $team,
                'event' => [Event::INTEGRATION_ENABLED, Event::TYPE_USER],
                'alertType' => AlertType::OTHER_ALERTS,
                'alertSubtype' => AlertSubType::SUBTYPE_CLIENTS_ACCOUNTS,
            ],
        ];
    }
}
