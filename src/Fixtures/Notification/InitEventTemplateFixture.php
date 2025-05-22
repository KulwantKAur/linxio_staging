<?php

namespace App\Fixtures\Notification;

use App\Entity\Notification\Event;
use App\Entity\Notification\EventTemplate;
use App\Entity\Notification\Template;
use App\Entity\Notification\TemplateSet;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitEventTemplateFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const DATA = [
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CREATED_SYSTEM, Event::TYPE_SYSTEM],
            'template' => Template::USER_CREATED_SYSTEM_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CREATED, Event::TYPE_USER],
            'template' => Template::USER_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_BLOCKED, Event::TYPE_USER],
            'template' => Template::USER_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_DELETED, Event::TYPE_USER],
            'template' => Template::USER_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_PWD_RESET, Event::TYPE_USER],
            'template' => Template::USER_PSW_RESET_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ADMIN_USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
            'template' => Template::CLIENT_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
            'template' => Template::CLIENT_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
            'template' => Template::CLIENT_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
            'template' => Template::CLIENT_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
            'template' => Template::CLIENT_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
            'template' => Template::CLIENT_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
            'template' => Template::CLIENT_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
            'template' => Template::CLIENT_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
            'template' => Template::CLIENT_DEMO_EXPIRED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
            'template' => Template::CLIENT_DEMO_EXPIRED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
            'template' => Template::CLIENT_DEMO_EXPIRED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
            'template' => Template::CLIENT_DEMO_EXPIRED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
            'template' => Template::VEHICLE_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
            'template' => Template::VEHICLE_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
            'template' => Template::VEHICLE_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
            'template' => Template::VEHICLE_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
            'template' => Template::VEHICLE_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
            'template' => Template::VEHICLE_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
            'template' => Template::VEHICLE_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_REGNO, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_REGNO_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_REGNO, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_REGNO_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_REGNO, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_REGNO_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_REGNO, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_REGNO_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_MODEL, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_MODEL_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_MODEL, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_MODEL_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_MODEL, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_MODEL_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_CHANGED_MODEL, Event::TYPE_USER],
            'template' => Template::VEHICLE_CHANGED_MODEL_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
            'template' => Template::VEHICLE_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRE_SOON_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRE_SOON_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRE_SOON_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DOCUMENT_EXPIRE_SOON_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_VOLTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_VOLTAGE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_VOLTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_VOLTAGE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_VOLTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_VOLTAGE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_VOLTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_VOLTAGE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_ENTER, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_ENTER_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_ENTER, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_ENTER_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_ENTER, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_ENTER_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_ENTER, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_ENTER_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_SOON, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_SOON_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_SOON, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_SOON_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_SOON, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_SOON_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_SOON, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_SOON_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_EXPIRED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_EXPIRED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_EXPIRED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_EXPIRED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DONE, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DONE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DONE, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DONE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DONE, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DONE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DONE, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DONE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DELETED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DELETED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DELETED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REMINDER_DELETED, Event::TYPE_USER],
            'template' => Template::SERVICE_REMINDER_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PANIC_BUTTON, Event::TYPE_USER],
            'template' => Template::PANIC_BUTTON_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PANIC_BUTTON, Event::TYPE_USER],
            'template' => Template::PANIC_BUTTON_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PANIC_BUTTON, Event::TYPE_USER],
            'template' => Template::PANIC_BUTTON_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PANIC_BUTTON, Event::TYPE_USER],
            'template' => Template::PANIC_BUTTON_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DRIVING_WITHOUT_DRIVER, Event::TYPE_USER],
            'template' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DRIVING_WITHOUT_DRIVER, Event::TYPE_USER],
            'template' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DRIVING_WITHOUT_DRIVER, Event::TYPE_USER],
            'template' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_DRIVING_WITHOUT_DRIVER, Event::TYPE_USER],
            'template' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_TOWING_EVENT, Event::TYPE_USER],
            'template' => Template::VEHICLE_TOWING_EVENT_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_TOWING_EVENT, Event::TYPE_USER],
            'template' => Template::VEHICLE_TOWING_EVENT_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_TOWING_EVENT, Event::TYPE_USER],
            'template' => Template::VEHICLE_TOWING_EVENT_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_TOWING_EVENT, Event::TYPE_USER],
            'template' => Template::VEHICLE_TOWING_EVENT_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_STANDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_STANDING_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_STANDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_STANDING_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_STANDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_STANDING_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_STANDING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_STANDING_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_DRIVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_DRIVING_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_DRIVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_DRIVING_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_DRIVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_DRIVING_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_LONG_DRIVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_LONG_DRIVING_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_MOVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_MOVING_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_MOVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_MOVING_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_MOVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_MOVING_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_MOVING, Event::TYPE_USER],
            'template' => Template::VEHICLE_MOVING_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_EXCESSING_IDLING, Event::TYPE_USER],
            'template' => Template::VEHICLE_EXCESSING_IDLING_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_EXCESSING_IDLING, Event::TYPE_USER],
            'template' => Template::VEHICLE_EXCESSING_IDLING_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_EXCESSING_IDLING, Event::TYPE_USER],
            'template' => Template::VEHICLE_EXCESSING_IDLING_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_EXCESSING_IDLING, Event::TYPE_USER],
            'template' => Template::VEHICLE_EXCESSING_IDLING_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::VEHICLE_UNAVAILABLE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::VEHICLE_UNAVAILABLE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::VEHICLE_UNAVAILABLE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::VEHICLE_UNAVAILABLE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OFFLINE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OFFLINE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OFFLINE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_OFFLINE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_LEAVE, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_LEAVE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_LEAVE, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_LEAVE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_LEAVE, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_LEAVE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_GEOFENCE_LEAVE, Event::TYPE_USER],
            'template' => Template::VEHICLE_GEOFENCE_LEAVE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_REASSIGNED, Event::TYPE_USER],
            'template' => Template::VEHICLE_REASSIGNED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_REASSIGNED, Event::TYPE_USER],
            'template' => Template::VEHICLE_REASSIGNED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_REASSIGNED, Event::TYPE_USER],
            'template' => Template::VEHICLE_REASSIGNED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_REASSIGNED, Event::TYPE_USER],
            'template' => Template::VEHICLE_REASSIGNED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_CLIENT, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_CLIENT_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_CLIENT, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_CLIENT_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_CLIENT, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_CLIENT_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_CLIENT, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_CLIENT_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_USER, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_USER_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_USER, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_USER_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_USER, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_USER_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::LOGIN_AS_USER, Event::TYPE_USER],
            'template' => Template::LOGIN_AS_USER_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_ONLINE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_ONLINE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_ONLINE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
            'template' => Template::VEHICLE_ONLINE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_NAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_NAME_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_SURNAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_SURNAME_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_SURNAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_SURNAME_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_SURNAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_SURNAME_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::USER_CHANGED_SURNAME, Event::TYPE_USER],
            'template' => Template::USER_CHANGED_SURNAME_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_IN_STOCK, Event::TYPE_USER],
            'template' => Template::DEVICE_IN_STOCK_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_IN_STOCK, Event::TYPE_USER],
            'template' => Template::DEVICE_IN_STOCK_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_IN_STOCK, Event::TYPE_USER],
            'template' => Template::DEVICE_IN_STOCK_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_IN_STOCK, Event::TYPE_USER],
            'template' => Template::DEVICE_IN_STOCK_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_OFFLINE, Event::TYPE_USER],
            'template' => Template::DEVICE_OFFLINE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_OFFLINE, Event::TYPE_USER],
            'template' => Template::DEVICE_OFFLINE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_OFFLINE, Event::TYPE_USER],
            'template' => Template::DEVICE_OFFLINE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_OFFLINE, Event::TYPE_USER],
            'template' => Template::DEVICE_OFFLINE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::DEVICE_UNAVAILABLE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::DEVICE_UNAVAILABLE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::DEVICE_UNAVAILABLE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNAVAILABLE, Event::TYPE_USER],
            'template' => Template::DEVICE_UNAVAILABLE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_DELETED, Event::TYPE_USER],
            'template' => Template::DEVICE_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_DELETED, Event::TYPE_USER],
            'template' => Template::DEVICE_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_DELETED, Event::TYPE_USER],
            'template' => Template::DEVICE_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_DELETED, Event::TYPE_USER],
            'template' => Template::DEVICE_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNKNOWN_DETECTED, Event::TYPE_USER],
            'template' => Template::DEVICE_UNKNOWN_DETECTED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNKNOWN_DETECTED, Event::TYPE_USER],
            'template' => Template::DEVICE_UNKNOWN_DETECTED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNKNOWN_DETECTED, Event::TYPE_USER],
            'template' => Template::DEVICE_UNKNOWN_DETECTED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_UNKNOWN_DETECTED, Event::TYPE_USER],
            'template' => Template::DEVICE_UNKNOWN_DETECTED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_RECORD_ADDED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_RECORD_ADDED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_RECORD_ADDED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DOCUMENT_RECORD_ADDED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_RECORD_ADDED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_RECORD_ADDED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_RECORD_ADDED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_RECORD_ADDED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REPAIR_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_REPAIR_ADDED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REPAIR_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_REPAIR_ADDED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REPAIR_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_REPAIR_ADDED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SERVICE_REPAIR_ADDED, Event::TYPE_USER],
            'template' => Template::SERVICE_REPAIR_ADDED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ODOMETER_CORRECTED, Event::TYPE_USER],
            'template' => Template::ODOMETER_CORRECTED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ODOMETER_CORRECTED, Event::TYPE_USER],
            'template' => Template::ODOMETER_CORRECTED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ODOMETER_CORRECTED, Event::TYPE_USER],
            'template' => Template::ODOMETER_CORRECTED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ODOMETER_CORRECTED, Event::TYPE_USER],
            'template' => Template::ODOMETER_CORRECTED_USER_MOBILE,
        ],

        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_WITH_FAIL, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_WITH_FAIL_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_WITH_FAIL, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_WITH_FAIL_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_WITH_FAIL, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_WITH_FAIL_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_WITH_FAIL, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_WITH_FAIL_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_USER],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_SYSTEM],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_SYSTEM],
            'template' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_TEMPERATURE, Event::TYPE_USER],
            'template' => Template::SENSOR_TEMPERATURE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_TEMPERATURE, Event::TYPE_USER],
            'template' => Template::SENSOR_TEMPERATURE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_TEMPERATURE, Event::TYPE_USER],
            'template' => Template::SENSOR_TEMPERATURE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_TEMPERATURE, Event::TYPE_USER],
            'template' => Template::SENSOR_TEMPERATURE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_HUMIDITY, Event::TYPE_USER],
            'template' => Template::SENSOR_HUMIDITY_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_HUMIDITY, Event::TYPE_USER],
            'template' => Template::SENSOR_HUMIDITY_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_HUMIDITY, Event::TYPE_USER],
            'template' => Template::SENSOR_HUMIDITY_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_HUMIDITY, Event::TYPE_USER],
            'template' => Template::SENSOR_HUMIDITY_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_LIGHT, Event::TYPE_USER],
            'template' => Template::SENSOR_LIGHT_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_LIGHT, Event::TYPE_USER],
            'template' => Template::SENSOR_LIGHT_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_LIGHT, Event::TYPE_USER],
            'template' => Template::SENSOR_LIGHT_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_LIGHT, Event::TYPE_USER],
            'template' => Template::SENSOR_LIGHT_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_BATTERY_LEVEL, Event::TYPE_USER],
            'template' => Template::SENSOR_BATTERY_LEVEL_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_BATTERY_LEVEL, Event::TYPE_USER],
            'template' => Template::SENSOR_BATTERY_LEVEL_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_BATTERY_LEVEL, Event::TYPE_USER],
            'template' => Template::SENSOR_BATTERY_LEVEL_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_BATTERY_LEVEL, Event::TYPE_USER],
            'template' => Template::SENSOR_BATTERY_LEVEL_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_STATUS_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_STATUS_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_STATUS_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_STATUS_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_EXPIRED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DRIVER_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_EXPIRED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_EXPIRED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DOCUMENT_RECORD_ADDED, Event::TYPE_USER],
            'template' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_IO_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_IO_STATUS_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_IO_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_IO_STATUS_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_IO_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_IO_STATUS_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::SENSOR_IO_STATUS, Event::TYPE_USER],
            'template' => Template::SENSOR_IO_STATUS_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_CREATED, Event::TYPE_USER],
            'template' => Template::ASSET_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_CREATED, Event::TYPE_USER],
            'template' => Template::ASSET_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_CREATED, Event::TYPE_USER],
            'template' => Template::ASSET_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_CREATED, Event::TYPE_USER],
            'template' => Template::ASSET_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DELETED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DELETED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DELETED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_DELETED, Event::TYPE_USER],
            'template' => Template::ASSET_DELETED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_MISSED, Event::TYPE_USER],
            'template' => Template::ASSET_MISSED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_MISSED, Event::TYPE_USER],
            'template' => Template::ASSET_MISSED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_MISSED, Event::TYPE_USER],
            'template' => Template::ASSET_MISSED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ASSET_MISSED, Event::TYPE_USER],
            'template' => Template::ASSET_MISSED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_BATTERY_PERCENTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_BATTERY_PERCENTAGE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_BATTERY_PERCENTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_BATTERY_PERCENTAGE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_BATTERY_PERCENTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_BATTERY_PERCENTAGE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_BATTERY_PERCENTAGE, Event::TYPE_USER],
            'template' => Template::TRACKER_BATTERY_PERCENTAGE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::STRIPE_INTEGRATION_ERROR_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::STRIPE_INTEGRATION_ERROR_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::STRIPE_INTEGRATION_ERROR_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::STRIPE_INTEGRATION_ERROR_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_FAILED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_FAILED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_FAILED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_FAILED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::STRIPE_PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INTEGRATION_ERROR_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INTEGRATION_ERROR_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INTEGRATION_ERROR_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INTEGRATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INTEGRATION_ERROR_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATION_ERROR_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATION_ERROR_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATION_ERROR_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATION_ERROR_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_INVOICE_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATION_ERROR_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATION_ERROR_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATION_ERROR_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATION_ERROR, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATION_ERROR_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::XERO_PAYMENT_CREATED, Event::TYPE_USER],
            'template' => Template::XERO_PAYMENT_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::PAYMENT_FAILED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::PAYMENT_FAILED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::PAYMENT_FAILED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_FAILED, Event::TYPE_USER],
            'template' => Template::PAYMENT_FAILED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::PAYMENT_SUCCESSFUL_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::PAYMENT_SUCCESSFUL_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::PAYMENT_SUCCESSFUL_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::PAYMENT_SUCCESSFUL, Event::TYPE_USER],
            'template' => Template::PAYMENT_SUCCESSFUL_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_WEB,
        ],

        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_CREATED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_CREATED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INVOICE_OVERDUE_BLOCKED_ADMIN, Event::TYPE_USER],
            'template' => Template::INVOICE_OVERDUE_BLOCKED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_CONTRACT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DEVICE_CONTRACT_EXPIRED_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_CONTRACT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DEVICE_CONTRACT_EXPIRED_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_CONTRACT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DEVICE_CONTRACT_EXPIRED_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_CONTRACT_EXPIRED, Event::TYPE_USER],
            'template' => Template::DEVICE_CONTRACT_EXPIRED_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_JAMMER_STARTED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_JAMMER_STARTED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_JAMMER_STARTED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_JAMMER_STARTED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_JAMMER_STARTED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_JAMMER_STARTED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_JAMMER_STARTED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_JAMMER_STARTED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_ACCIDENT_HAPPENED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_ACCIDENT_HAPPENED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_ACCIDENT_HAPPENED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_ACCIDENT_HAPPENED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_ACCIDENT_HAPPENED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_ACCIDENT_HAPPENED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::TRACKER_ACCIDENT_HAPPENED_ALARM, Event::TYPE_USER],
            'template' => Template::TRACKER_ACCIDENT_HAPPENED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_REPLACED, Event::TYPE_USER],
            'template' => Template::DEVICE_REPLACED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_REPLACED, Event::TYPE_USER],
            'template' => Template::DEVICE_REPLACED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_REPLACED, Event::TYPE_USER],
            'template' => Template::DEVICE_REPLACED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::DEVICE_REPLACED, Event::TYPE_USER],
            'template' => Template::DEVICE_REPLACED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::EXCEEDING_SPEED_LIMIT, Event::TYPE_USER],
            'template' => Template::EXCEEDING_SPEED_LIMIT_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::EXCEEDING_SPEED_LIMIT, Event::TYPE_USER],
            'template' => Template::EXCEEDING_SPEED_LIMIT_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::EXCEEDING_SPEED_LIMIT, Event::TYPE_USER],
            'template' => Template::EXCEEDING_SPEED_LIMIT_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::EXCEEDING_SPEED_LIMIT, Event::TYPE_USER],
            'template' => Template::EXCEEDING_SPEED_LIMIT_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INTEGRATION_ENABLED, Event::TYPE_USER],
            'template' => Template::INTEGRATION_ENABLED_USER_WEB,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INTEGRATION_ENABLED, Event::TYPE_USER],
            'template' => Template::INTEGRATION_ENABLED_USER_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INTEGRATION_ENABLED, Event::TYPE_USER],
            'template' => Template::INTEGRATION_ENABLED_USER_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::INTEGRATION_ENABLED, Event::TYPE_USER],
            'template' => Template::INTEGRATION_ENABLED_USER_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
            'template' => Template::ACCESS_LEVEL_CHANGED_EMAIL,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
            'template' => Template::ACCESS_LEVEL_CHANGED_SMS,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
            'template' => Template::ACCESS_LEVEL_CHANGED_MOBILE,
        ],
        [
            'set' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME,
            'event' => [Event::ACCESS_LEVEL_CHANGED, Event::TYPE_USER],
            'template' => Template::ACCESS_LEVEL_CHANGED_WEB,
        ],
    ];

    public function getDependencies(): array
    {
        return [
            InitTemplateSetFixture::class,
            InitEventsFixture::class,
            InitTemplatesFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::DATA as $data) {
            $eventTemplate = $manager->getRepository(EventTemplate::class)->findOneBy(
                [
                    'set' => $this->getReference($data['set']),
                    'event' => $this->getReference(implode('_', $data['event'])),
                    'template' => $this->getReference($data['template'])
                ]
            );
            if (!$eventTemplate) {
                $eventTemplate = new EventTemplate();
                $eventTemplate->setSet($this->getReference($data['set']));
                $eventTemplate->setEvent($this->getReference(implode('_', $data['event'])));
                $eventTemplate->setTemplate($this->getReference($data['template']));
                $manager->persist($eventTemplate);
            }
        }

        $manager->flush();
    }
}
