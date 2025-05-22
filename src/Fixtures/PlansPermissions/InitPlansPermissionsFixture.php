<?php

namespace App\Fixtures\PlansPermissions;

use App\Entity\Permission;
use App\Entity\Plan;
use App\Entity\PlanRolePermission;
use App\Entity\Role;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Plans\InitPlansFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitPlansPermissionsFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return array(
            InitTeamsFixture::class,
            InitRolesFixture::class,
            InitPlansFixture::class,
        );
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public const PLANS_PERMISSIONS = [
        Plan::PLAN_STARTER => [
            'id' => 1,
            'roles' => [
                Role::ROLE_CLIENT_ADMIN => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,
                    Permission::DEVICES_EDIT_RAW_DATA_LOG,
                    Permission::DEVICE_EDIT,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,

                    Permission::SUPPORT_SECTION,

                    Permission::VEHICLE_EDIT,
                    Permission::VEHICLE_DELETE,
                    Permission::VEHICLE_ARCHIVE,

                    Permission::SETTING_SET,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::CLIENT_DELETE_USER,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::SET_MOBILE_DEVICE,
                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::DRIVER_BLOCK_USER,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::DRIVER_DELETE,
                    Permission::DRIVER_ARCHIVE,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,

                    Permission::CHAT_LIST,
                    Permission::CHAT_LIST_ALL,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,

                    Permission::FUEL_RECORD_UPDATE,
                ],
                Role::ROLE_MANAGER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,
                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,

                    Permission::SUPPORT_SECTION,

                    Permission::VEHICLE_EDIT,

                    Permission::SETTING_SET,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,

                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,
                ],
                Role::ROLE_CLIENT_DRIVER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::SUPPORT_SECTION,

                    Permission::MAP_VIEW,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::LOGIN_WITH_ID,
                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,
                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,
                ],
                Role::ROLE_CLIENT_INSTALLER => [
                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,

                    Permission::SUPPORT_SECTION,

                    Permission::DEVICE_LIST,

                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                ],
            ],
        ],
        Plan::PLAN_ESSENTIALS => [
            'id' => 2,
            'roles' => [
                Role::ROLE_CLIENT_ADMIN => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,
                    Permission::MAP_SECTION_GEOFENCES,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_DASHBOARD,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::DRIVERS_SECTION,
                    Permission::DRIVERS_ADD_DRIVER,
                    Permission::DRIVERS_EDIT_PROFILE_INFO,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,
                    Permission::DEVICES_EDIT_RAW_DATA_LOG,
                    Permission::DEVICE_EDIT,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_NOTIFICATIONS,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,
                    Permission::CONFIGURATION_GEOFENCES,
                    Permission::CONFIGURATION_INTEGRATIONS_LIST,

                    Permission::SUPPORT_SECTION,

                    Permission::NOTIFICATION_NEW,
                    Permission::NOTIFICATION_LIST,
                    Permission::NOTIFICATION_EDIT,
                    Permission::NOTIFICATION_DELETE,

                    Permission::VEHICLE_EDIT,
                    Permission::VEHICLE_DELETE,
                    Permission::VEHICLE_ARCHIVE,

                    Permission::SETTING_SET,

                    Permission::AREA_LIST,
                    Permission::AREA_NEW,
                    Permission::AREA_EDIT,
                    Permission::AREA_ARCHIVE,
                    Permission::AREA_DELETE,
                    Permission::AREA_GROUP_NEW,
                    Permission::AREA_GROUP_EDIT,
                    Permission::AREA_GROUP_DELETE,
                    Permission::AREA_GROUP_ARCHIVE,
                    Permission::AREA_GROUP_LIST,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::CLIENT_DELETE_USER,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::DRIVER_LIST,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,

                    Permission::SET_MOBILE_DEVICE,
                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::SCHEDULED_REPORT_LIST,
                    Permission::SCHEDULED_REPORT_CREATE,
                    Permission::SCHEDULED_REPORT_EDIT,
                    Permission::SCHEDULED_REPORT_DELETE,

                    Permission::DRIVER_BLOCK_USER,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::DRIVER_DELETE,
                    Permission::DRIVER_ARCHIVE,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_DELETE,
                    Permission::DRIVER_DOCUMENT_ARCHIVE,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,

                    Permission::CHAT_LIST,
                    Permission::CHAT_LIST_ALL,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,
                    Permission::FUEL_RECORD_UPDATE,
                ],
                Role::ROLE_MANAGER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,
                    Permission::MAP_SECTION_GEOFENCES,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_DASHBOARD,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::DRIVERS_SECTION,
                    Permission::DRIVERS_EDIT_PROFILE_INFO,
                    Permission::DRIVERS_ADD_DRIVER,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_NOTIFICATIONS,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,
                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,
                    Permission::CONFIGURATION_INTEGRATIONS_LIST,
                    Permission::CONFIGURATION_GEOFENCES,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,

                    Permission::SUPPORT_SECTION,

                    Permission::NOTIFICATION_NEW,
                    Permission::NOTIFICATION_LIST,
                    Permission::NOTIFICATION_EDIT,
                    Permission::NOTIFICATION_DELETE,

                    Permission::VEHICLE_EDIT,

                    Permission::SETTING_SET,

                    Permission::AREA_NEW,
                    Permission::AREA_DELETE,
                    Permission::AREA_EDIT,
                    Permission::AREA_ARCHIVE,
                    Permission::AREA_GROUP_DELETE,
                    Permission::AREA_GROUP_ARCHIVE,
                    Permission::AREA_GROUP_EDIT,
                    Permission::AREA_GROUP_LIST,
                    Permission::AREA_GROUP_NEW,
                    Permission::AREA_LIST,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::DRIVER_LIST,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,

                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::SCHEDULED_REPORT_LIST,
                    Permission::SCHEDULED_REPORT_CREATE,
                    Permission::SCHEDULED_REPORT_EDIT,
                    Permission::SCHEDULED_REPORT_DELETE,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::DRIVER_DOCUMENT_DELETE,
                    Permission::DRIVER_DOCUMENT_ARCHIVE,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,

                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,
                ],
                Role::ROLE_CLIENT_DRIVER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::DRIVERS_EDIT_PROFILE_INFO,

                    Permission::SUPPORT_SECTION,

                    Permission::DRIVER_LIST,

                    Permission::AREA_GROUP_LIST,
                    Permission::AREA_LIST,

                    Permission::MAP_VIEW,

                    Permission::NOTIFICATION_LIST,

                    Permission::LOGIN_WITH_ID,

                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,
                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,
                ],
                Role::ROLE_CLIENT_INSTALLER => [
                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,

                    Permission::SUPPORT_SECTION,

                    Permission::DEVICE_LIST,

                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                ],
            ],
        ],
        Plan::PLAN_PLUS => [
            'id' => 3,
            'roles' => [
                Role::ROLE_CLIENT_ADMIN => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,
                    Permission::MAP_SECTION_GEOFENCES,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_DASHBOARD,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::FUEL_SECTION,
                    Permission::FUEL_SUMMARY,
                    Permission::FUEL_RECORDS,
                    Permission::FUEL_IMPORT_DATA,

                    Permission::DRIVERS_SECTION,
                    Permission::DRIVERS_ADD_DRIVER,
                    Permission::DRIVERS_EDIT_PROFILE_INFO,

                    Permission::DRIVING_BEHAVIOUR_SECTION,
                    Permission::DRIVING_BEHAVIOUR_DASHBOARD,
                    Permission::DRIVING_BEHAVIOUR_VEHICLES,
                    Permission::DRIVING_BEHAVIOUR_DRIVERS,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,
                    Permission::DEVICES_EDIT_RAW_DATA_LOG,
                    Permission::DEVICE_EDIT,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_TEMPLATES,
                    Permission::CONFIGURATION_TEMPLATES_EDIT,
                    Permission::CONFIGURATION_FLEET,
                    Permission::SCHEDULED_REPORT_LIST,
                    Permission::SCHEDULED_REPORT_CREATE,
                    Permission::SCHEDULED_REPORT_EDIT,
                    Permission::SCHEDULED_REPORT_DELETE,
                    Permission::CONFIGURATION_NOTIFICATIONS,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::CONFIGURATION_GEOFENCES,
                    Permission::CONFIGURATION_INTEGRATIONS_LIST,

                    Permission::SUPPORT_SECTION,

                    Permission::NOTIFICATION_NEW,
                    Permission::NOTIFICATION_LIST,
                    Permission::NOTIFICATION_EDIT,
                    Permission::NOTIFICATION_DELETE,

                    Permission::REPAIR_COST_NEW,
                    Permission::REPAIR_COST_EDIT,
                    Permission::REPAIR_COST_DELETE,
                    Permission::REPAIR_COST_LIST,

                    Permission::VEHICLE_EDIT,
                    Permission::VEHICLE_DELETE,
                    Permission::VEHICLE_ARCHIVE,

                    Permission::SETTING_SET,

                    Permission::AREA_LIST,
                    Permission::AREA_NEW,
                    Permission::AREA_EDIT,
                    Permission::AREA_ARCHIVE,
                    Permission::AREA_DELETE,
                    Permission::AREA_GROUP_LIST,
                    Permission::AREA_GROUP_NEW,
                    Permission::AREA_GROUP_EDIT,
                    Permission::AREA_GROUP_DELETE,
                    Permission::AREA_GROUP_ARCHIVE,

                    Permission::VEHICLE_GROUP_NEW,
                    Permission::VEHICLE_GROUP_EDIT,
                    Permission::VEHICLE_GROUP_LIST,
                    Permission::VEHICLE_GROUP_DELETE,
                    Permission::VEHICLE_GROUP_ARCHIVE,
                    Permission::DEPOT_NEW,
                    Permission::DEPOT_EDIT,
                    Permission::DEPOT_DELETE,
                    Permission::DEPOT_ARCHIVE,
                    Permission::DEPOT_LIST,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::CLIENT_DELETE_USER,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::DRIVER_LIST,

                    Permission::VEHICLE_REMINDER_NEW,
                    Permission::VEHICLE_REMINDER_EDIT,
                    Permission::VEHICLE_REMINDER_LIST,
                    Permission::VEHICLE_REMINDER_DELETE,
                    Permission::VEHICLE_REMINDER_ARCHIVE,

                    Permission::VEHICLE_SERVICE_RECORD_NEW,
                    Permission::VEHICLE_SERVICE_RECORD_LIST,
                    Permission::VEHICLE_SERVICE_RECORD_DELETE,
                    Permission::VEHICLE_SERVICE_RECORD_EDIT,

                    Permission::VEHICLE_DOCUMENT_NEW,
                    Permission::VEHICLE_DOCUMENT_EDIT,
                    Permission::VEHICLE_DOCUMENT_LIST,
                    Permission::VEHICLE_DOCUMENT_DELETE,
                    Permission::VEHICLE_DOCUMENT_ARCHIVE,
                    Permission::VEHICLE_DOCUMENT_RECORD_NEW,
                    Permission::VEHICLE_DOCUMENT_RECORD_LIST,

                    Permission::INSPECTION_FORM_FILL,
                    Permission::INSPECTION_FORM_FILLED,

                    Permission::DIGITAL_FORM_LIST,
                    Permission::DIGITAL_FORM_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_CREATE,

                    Permission::FUEL_IGNORE_NEW,
                    Permission::FUEL_IGNORE_EDIT,
                    Permission::FUEL_IGNORE_LIST,
                    Permission::FUEL_IGNORE_DELETE,

                    Permission::FUEL_MAPPING_NEW,
                    Permission::FUEL_MAPPING_EDIT,
                    Permission::FUEL_MAPPING_LIST,
                    Permission::FUEL_MAPPING_DELETE,
                    Permission::FUEL_TYPES_LIST,

                    Permission::FUEL_SUMMARY,
                    Permission::FUEL_RECORDS,
                    Permission::FUEL_IMPORT_DATA,
                    Permission::FUEL_FILE_UPDATE,
                    Permission::FUEL_FILE_DELETE,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::SET_MOBILE_DEVICE,

                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,
                    Permission::CONFIGURATION_USER_GROUP_LIST_AREAS,
                    Permission::CONFIGURATION_USER_GROUP_LIST_MODULES,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,
                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::VEHICLE_INSPECTION_FORM_LIST,
                    Permission::VEHICLE_INSPECTION_FORM_CREATE,
                    Permission::VEHICLE_INSPECTION_FORM_EDIT,
                    Permission::VEHICLE_INSPECTION_FORM_DELETE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::DRIVER_BLOCK_USER,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::DRIVER_DELETE,
                    Permission::DRIVER_ARCHIVE,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_DELETE,
                    Permission::DRIVER_DOCUMENT_ARCHIVE,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,

                    Permission::CHAT_LIST,
                    Permission::CHAT_LIST_ALL,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,
                    Permission::FUEL_RECORD_UPDATE,
                    Permission::CAMERAS,
                ],
                Role::ROLE_MANAGER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,
                    Permission::MAP_SECTION_GEOFENCES,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_DASHBOARD,
                    Permission::FLEET_SECTION_FLEET,
                    Permission::FLEET_SECTION_ADD_VEHICLE,

                    Permission::FUEL_SECTION,
                    Permission::FUEL_SUMMARY,
                    Permission::FUEL_RECORDS,
                    Permission::FUEL_IMPORT_DATA,

                    Permission::DRIVERS_SECTION,
                    Permission::DRIVERS_EDIT_PROFILE_INFO,

                    Permission::DRIVING_BEHAVIOUR_SECTION,
                    Permission::DRIVING_BEHAVIOUR_DASHBOARD,
                    Permission::DRIVING_BEHAVIOUR_VEHICLES,
                    Permission::DRIVING_BEHAVIOUR_DRIVERS,

                    Permission::ALERTS_SECTION,

                    Permission::REPORTS_SECTION,

                    Permission::DEVICE,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_DRIVING_OPTIONS,
                    Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT,
                    Permission::CONFIGURATION_FLEET,
                    Permission::CONFIGURATION_NOTIFICATIONS,
                    Permission::CONFIGURATION_USERS,
                    Permission::CONFIGURATION_INTEGRATIONS_LIST,
                    Permission::CONFIGURATION_GEOFENCES,
                    Permission::CONFIGURATION_USER_GROUPS,
                    Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES,
                    Permission::CONFIGURATION_USER_GROUP_LIST_AREAS,
                    Permission::CONFIGURATION_USER_GROUP_LIST_MODULES,
                    Permission::USER_GROUP_NEW,
                    Permission::USER_GROUP_EDIT,
                    Permission::USER_GROUP_DELETE,
                    Permission::USER_GROUP_ARCHIVE,
                    Permission::CONFIGURATION_TEMPLATES,
                    Permission::CONFIGURATION_TEMPLATES_EDIT,

                    Permission::SUPPORT_SECTION,

                    Permission::NOTIFICATION_NEW,
                    Permission::NOTIFICATION_LIST,
                    Permission::NOTIFICATION_EDIT,
                    Permission::NOTIFICATION_DELETE,

                    Permission::REPAIR_COST_NEW,
                    Permission::REPAIR_COST_EDIT,
                    Permission::REPAIR_COST_DELETE,
                    Permission::REPAIR_COST_LIST,

                    Permission::VEHICLE_EDIT,

                    Permission::SETTING_SET,

                    Permission::AREA_NEW,
                    Permission::AREA_DELETE,
                    Permission::AREA_EDIT,
                    Permission::AREA_ARCHIVE,
                    Permission::AREA_GROUP_DELETE,
                    Permission::AREA_GROUP_ARCHIVE,
                    Permission::AREA_GROUP_EDIT,
                    Permission::AREA_GROUP_LIST,
                    Permission::AREA_GROUP_NEW,
                    Permission::AREA_LIST,

                    Permission::VEHICLE_GROUP_NEW,
                    Permission::VEHICLE_GROUP_EDIT,
                    Permission::VEHICLE_GROUP_LIST,
                    Permission::VEHICLE_GROUP_DELETE,
                    Permission::VEHICLE_GROUP_ARCHIVE,
                    Permission::DEPOT_NEW,
                    Permission::DEPOT_EDIT,
                    Permission::DEPOT_DELETE,
                    Permission::DEPOT_ARCHIVE,
                    Permission::DEPOT_LIST,

                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::USER_HISTORY_UPDATED,
                    Permission::USER_HISTORY_LAST_LOGIN,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,

                    Permission::DRIVER_LIST,

                    Permission::VEHICLE_REMINDER_NEW,
                    Permission::VEHICLE_REMINDER_EDIT,
                    Permission::VEHICLE_REMINDER_LIST,
                    Permission::VEHICLE_REMINDER_DELETE,
                    Permission::VEHICLE_REMINDER_ARCHIVE,

                    Permission::VEHICLE_SERVICE_RECORD_NEW,
                    Permission::VEHICLE_SERVICE_RECORD_LIST,
                    Permission::VEHICLE_SERVICE_RECORD_DELETE,
                    Permission::VEHICLE_SERVICE_RECORD_EDIT,

                    Permission::VEHICLE_DOCUMENT_NEW,
                    Permission::VEHICLE_DOCUMENT_EDIT,
                    Permission::VEHICLE_DOCUMENT_LIST,
                    Permission::VEHICLE_DOCUMENT_ARCHIVE,
                    Permission::VEHICLE_DOCUMENT_RECORD_NEW,
                    Permission::VEHICLE_DOCUMENT_RECORD_LIST,

                    Permission::INSPECTION_FORM_FILL,
                    Permission::INSPECTION_FORM_FILLED,

                    Permission::DIGITAL_FORM_LIST,
                    Permission::DIGITAL_FORM_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_CREATE,

                    Permission::FUEL_IGNORE_NEW,
                    Permission::FUEL_IGNORE_EDIT,
                    Permission::FUEL_IGNORE_LIST,
                    Permission::FUEL_IGNORE_DELETE,

                    Permission::FUEL_MAPPING_NEW,
                    Permission::FUEL_MAPPING_EDIT,
                    Permission::FUEL_MAPPING_LIST,
                    Permission::FUEL_MAPPING_DELETE,
                    Permission::FUEL_TYPES_LIST,

                    Permission::FUEL_SUMMARY,
                    Permission::FUEL_RECORDS,
                    Permission::FUEL_IMPORT_DATA,
                    Permission::FUEL_FILE_UPDATE,
                    Permission::FUEL_FILE_DELETE,

                    Permission::MAP_VIEW,
                    Permission::DEVICE_LIST,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::VEHICLE_INSPECTION_FORM_LIST,
                    Permission::VEHICLE_INSPECTION_FORM_CREATE,
                    Permission::VEHICLE_INSPECTION_FORM_EDIT,
                    Permission::VEHICLE_INSPECTION_FORM_DELETE,

                    Permission::DEVICE_SENSOR_LIST,
                    Permission::DEVICE_SENSOR_EDIT,
                    Permission::DEVICE_SENSOR_CREATE,
                    Permission::DEVICE_SENSOR_DELETE,

                    Permission::SCHEDULED_REPORT_LIST,
                    Permission::SCHEDULED_REPORT_CREATE,
                    Permission::SCHEDULED_REPORT_EDIT,
                    Permission::SCHEDULED_REPORT_DELETE,

                    Permission::ASSET_NEW,
                    Permission::ASSET_EDIT,
                    Permission::ASSET_DELETE,
                    Permission::ASSET_LIST,
                    Permission::ASSET_INSTALL_UNINSTALL,
                    Permission::ASSET_DASHBOARD,
                    Permission::ASSET_REMINDER_LIST,
                    Permission::ASSET_REMINDER_NEW,
                    Permission::ASSET_REMINDER_EDIT,
                    Permission::ASSET_REMINDER_DELETE,
                    Permission::ASSET_REMINDER_ARCHIVE,
                    Permission::ASSET_SERVICE_RECORD_LIST,
                    Permission::ASSET_SERVICE_RECORD_NEW,
                    Permission::ASSET_SERVICE_RECORD_EDIT,
                    Permission::ASSET_SERVICE_RECORD_DELETE,
                    Permission::ASSET_DOCUMENT_LIST,
                    Permission::ASSET_DOCUMENT_NEW,
                    Permission::ASSET_DOCUMENT_EDIT,
                    Permission::ASSET_DOCUMENT_DELETE,
                    Permission::ASSET_DOCUMENT_ARCHIVE,
                    Permission::ASSET_DOCUMENT_RECORD_LIST,
                    Permission::ASSET_DOCUMENT_RECORD_NEW,
                    Permission::ASSET_SECTION,
                    Permission::ASSET_SECTION_SERVICE_REMINDERS,
                    Permission::ASSET_SECTION_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
                    Permission::ASSET_SECTION_EDIT_DOCUMENTS,
                    Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
                    Permission::MAP_SECTION_ASSETS,
                    Permission::DRIVER_RESET_PASSWORD,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::DRIVER_DOCUMENT_DELETE,
                    Permission::DRIVER_DOCUMENT_ARCHIVE,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,

                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,

                    Permission::BILLING,
                    Permission::BILLING_INVOICE_PAY,
                    Permission::BILLING_PAYMENT_CHANGE,
                    Permission::BILLING_INVOICE_VIEW,
                    Permission::CAMERAS,
                ],
                Role::ROLE_CLIENT_DRIVER => [
                    Permission::MAP_SECTION,
                    Permission::MAP_SECTION_VEHICLE,
                    Permission::MAP_SECTION_DRIVERS,

                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::DRIVERS_EDIT_PROFILE_INFO,

                    Permission::DRIVING_BEHAVIOUR_SECTION,
                    Permission::DRIVING_BEHAVIOUR_VEHICLES,
                    Permission::DRIVING_BEHAVIOUR_DRIVERS,

                    Permission::SUPPORT_SECTION,

                    Permission::DRIVER_LIST,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_ARCHIVE_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,

                    Permission::REPAIR_COST_NEW,
                    Permission::REPAIR_COST_EDIT,
                    Permission::REPAIR_COST_LIST,

                    Permission::VEHICLE_GROUP_LIST,
                    Permission::DEPOT_LIST,

                    Permission::VEHICLE_REMINDER_NEW,
                    Permission::VEHICLE_REMINDER_EDIT,
                    Permission::VEHICLE_REMINDER_LIST,

                    Permission::VEHICLE_SERVICE_RECORD_NEW,
                    Permission::VEHICLE_SERVICE_RECORD_LIST,
                    Permission::VEHICLE_SERVICE_RECORD_EDIT,

                    Permission::VEHICLE_DOCUMENT_NEW,
                    Permission::VEHICLE_DOCUMENT_EDIT,
                    Permission::VEHICLE_DOCUMENT_LIST,
                    Permission::VEHICLE_DOCUMENT_RECORD_NEW,
                    Permission::VEHICLE_DOCUMENT_RECORD_LIST,

                    Permission::INSPECTION_FORM_FILL,
                    Permission::INSPECTION_FORM_FILLED,

                    Permission::DIGITAL_FORM_LIST,
                    Permission::DIGITAL_FORM_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_VIEW,
                    Permission::DIGITAL_FORM_ANSWER_CREATE,

                    Permission::FUEL_IGNORE_EDIT,
                    Permission::FUEL_IGNORE_LIST,

                    Permission::FUEL_MAPPING_EDIT,
                    Permission::FUEL_MAPPING_LIST,
                    Permission::FUEL_TYPES_LIST,

                    Permission::MAP_VIEW,
                    Permission::REMINDER_CATEGORY_LIST,

                    Permission::NOTIFICATION_LIST,

                    Permission::LOGIN_WITH_ID,

                    Permission::SET_MOBILE_DEVICE_TOKEN,
                    Permission::TRACK_LINK_CREATE,

                    Permission::VEHICLE_INSPECTION_FORM_CREATE,
                    Permission::VEHICLE_INSPECTION_FORM_EDIT,
                    Permission::VEHICLE_INSPECTION_FORM_DELETE,
                    Permission::DRIVER_DOCUMENT_LIST,
                    Permission::DRIVER_DOCUMENT_NEW,
                    Permission::DRIVER_DOCUMENT_EDIT,
                    Permission::DRIVER_DOCUMENT_RECORD_LIST,
                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                    Permission::DRIVER_DOCUMENT_RECORD_NEW,
                    Permission::CHAT_LIST,
                    Permission::CHAT_CREATE,
                ],
                Role::ROLE_CLIENT_INSTALLER => [
                    Permission::FLEET_SECTION,
                    Permission::FLEET_SECTION_FLEET,

                    Permission::DEVICE,
                    Permission::DEVICE_INSTALL_UNINSTALL,

                    Permission::CONFIGURATION_SECTION,
                    Permission::CONFIGURATION_COMPANY_INFO,

                    Permission::SUPPORT_SECTION,

                    Permission::DEVICE_LIST,

                    Permission::SUPPORT_SUBMIT_TICKET,
                    Permission::SUPPORT_CONTACT_US,
                ],
            ],
        ]
    ];

    public const ADMIN_ROLES_PERMISSIONS = [
        Permission::CLIENT_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER,
            Role::ROLE_INSTALLER
        ],
        Permission::NEW_CLIENT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::CLIENT_NEW_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_EDIT_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_ARCHIVE_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_BLOCK_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_USER_RESET_PWD => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_DELETE_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::CLIENT_STATUS_HISTORY => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_UPDATED_HISTORY => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],
        Permission::CLIENT_NOTES_HISTORY => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_CREATED_HISTORY => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],

        Permission::ADMIN_TEAM_USER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ADMIN_TEAM_NEW_USER => [Role::ROLE_SUPER_ADMIN],
        Permission::ADMIN_TEAM_EDIT_USER => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],
        Permission::ADMIN_TEAM_DELETE_USER => [Role::ROLE_SUPER_ADMIN],

        Permission::USER_HISTORY_CREATED => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],
        Permission::USER_HISTORY_UPDATED => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],
        Permission::USER_HISTORY_LAST_LOGIN => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN],

        Permission::DEVICE_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_INSTALL_UNINSTALL => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_CHANGE_TEAM => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_INSTALLER,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::VEHICLE_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::VEHICLE_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::VEHICLE_ARCHIVE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::VEHICLE_GROUP_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SETTING_SET => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::DEPOT_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::VEHICLE_REMINDER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::VEHICLE_SERVICE_RECORD_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::VEHICLE_DOCUMENT_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::AREA_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::AREA_GROUP_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::LOGIN_AS_CLIENT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::LOGIN_AS_USER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::FULL_SEARCH => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::DRIVER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::FUEL_IGNORE_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_IGNORE_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_IGNORE_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_IGNORE_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::FUEL_MAPPING_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_MAPPING_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_MAPPING_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_MAPPING_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FUEL_TYPES_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],

        Permission::MAP_VIEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::REMINDER_CATEGORY_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::REMINDER_CATEGORY_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::REMINDER_CATEGORY_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::REMINDER_CATEGORY_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::INSPECTION_FORM_FILL => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::INSPECTION_FORM_FILLED => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::INSPECTION_FORM_SET_TEAM => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DIGITAL_FORM_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DIGITAL_FORM_VIEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DIGITAL_FORM_ANSWER_VIEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DIGITAL_FORM_ANSWER_CREATE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::REPAIR_COST_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::NOTIFICATION_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::NOTIFICATION_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::NOTIFICATION_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::NOTIFICATION_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CLIENT_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ALERTS_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_COMPANY_INFO => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_COMPANY_INFO_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ADMIN_TEAM_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SUPPORT_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SET_MOBILE_DEVICE_TOKEN => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICES_VEHICLES_IMPORT_DATA => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_DRIVING_OPTIONS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_SENSOR_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_SENSOR_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_SENSOR_CREATE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::DEVICE_SENSOR_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_INSTALL_UNINSTALL => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_DASHBOARD => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_REMINDER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_DOCUMENT_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SCHEDULED_REPORT_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SCHEDULED_REPORT_CREATE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SCHEDULED_REPORT_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SCHEDULED_REPORT_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION_SERVICE_REMINDERS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION_DOCUMENTS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION_EDIT_DOCUMENTS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::ASSET_SECTION_EDIT_REPAIR_COSTS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::RESELLER_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::RESELLER_USER_NEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::RESELLER_USER_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_USER_DELETE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_USER_LIST => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_SECTION => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::LOGIN_AS_RESELLER => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ],
        Permission::RESELLER_NOTES_HISTORY => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::PLATFORM_SETTING_ADMIN_EDIT => [
            Role::ROLE_SUPER_ADMIN
        ],
        Permission::PLATFORM_SETTING_RESELLER_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::SUPPORT_SUBMIT_TICKET => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::SUPPORT_CONTACT_US => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_INSTALLER,
            Role::ROLE_SUPPORT,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_USERS => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::BILLING_PLAN_EDIT => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::FLEET_SECTION_ADD_VEHICLE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER,
            Role::ROLE_INSTALLER,
        ],
        Permission::MOVE_DEVICE => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN
        ],
        Permission::XERO_API => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::STRIPE_API => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::BILLING_INVOICE_VIEW => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::BILLING_ADMIN => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::FLEET_SECTION_FLEET => [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
            Role::ROLE_SALES_REP,
            Role::ROLE_ACCOUNT_MANAGER
        ],
        Permission::BILLING_INVOICE_CLEAN => [
            Role::ROLE_SUPER_ADMIN
        ],
    ];

    public const RESELLER_ROLES_PERMISSIONS = [
        Permission::RESELLER_EDIT => [
            Role::ROLE_RESELLER_ADMIN
        ],
        Permission::RESELLER_USER_NEW => [
            Role::ROLE_RESELLER_ADMIN
        ],
        Permission::RESELLER_USER_EDIT => [
            Role::ROLE_RESELLER_ADMIN
        ],
        Permission::RESELLER_USER_DELETE => [
            Role::ROLE_RESELLER_ADMIN
        ],
        Permission::RESELLER_USER_LIST => [
            Role::ROLE_RESELLER_ADMIN
        ],

        Permission::CLIENT_LIST => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::NEW_CLIENT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_NEW_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_EDIT_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_ARCHIVE_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_DELETE_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_STATUS_HISTORY => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_NOTES_HISTORY => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::RESELLER_NOTES_HISTORY => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_CREATED_HISTORY => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],

        Permission::DEVICE_NEW => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::DEVICE_LIST => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::DEVICE_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::DEVICE_DELETE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::DEVICE_INSTALL_UNINSTALL => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::NOTIFICATION_NEW => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::NOTIFICATION_LIST => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::NOTIFICATION_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::NOTIFICATION_DELETE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_SECTION => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::DEVICE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::RESELLER_TEAM_SECTION => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::ALERTS_SECTION => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CONFIGURATION_SECTION => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::SUPPORT_SECTION => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::PLATFORM_SETTING_RESELLER_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::SUPPORT_SUBMIT_TICKET => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::SUPPORT_CONTACT_US => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::CLIENT_BLOCK_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::CLIENT_USER_RESET_PWD => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::CONFIGURATION_COMPANY_INFO => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_COMPANY_INFO_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER
        ],
        Permission::CONFIGURATION_USERS => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER
        ],
        Permission::SETTING_SET => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER
        ],
        Permission::FLEET_SECTION_ADD_VEHICLE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_RESELLER_INSTALLER
        ],
        Permission::BILLING_PLAN_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
        ],
        Permission::BILLING_INVOICE_VIEW => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::FUEL_STATION_CREATE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::FUEL_STATION_EDIT => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::FUEL_STATION_DELETE => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::FUEL_STATION_LIST => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::FLEET_SECTION_FLEET => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
        Permission::LOGIN_AS_USER => [
            Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_RESELLER_SALES_REP,
            Role::ROLE_RESELLER_ACCOUNT_MANAGER,
        ],
    ];

    public const CLIENT_PLAN_MAX_PERMISSIONS = [
        Plan::PLAN_STARTER => self::PLANS_PERMISSIONS[Plan::PLAN_STARTER]['roles'][Role::ROLE_CLIENT_ADMIN],
        Plan::PLAN_ESSENTIALS => self::PLANS_PERMISSIONS[Plan::PLAN_ESSENTIALS]['roles'][Role::ROLE_CLIENT_ADMIN],
        Plan::PLAN_PLUS => self::PLANS_PERMISSIONS[Plan::PLAN_PLUS]['roles'][Role::ROLE_CLIENT_ADMIN],
    ];

    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        foreach (self::PLANS_PERMISSIONS as $plan) {
            $planEntity = $manager->getRepository(Plan::class)->find($plan['id']);
            foreach ($plan['roles'] as $role => $permissions) {
                $roleEntity = $manager->getRepository(Role::class)->findOneBy(
                    [
                        'name' => $role,
                        'team' => Team::TEAM_CLIENT
                    ]
                );

                //remove deleted permissions for this type of user
                $existedPermissions = $manager->getRepository(PlanRolePermission::class)->findBy(
                    [
                        'plan' => $planEntity,
                        'role' => $roleEntity
                    ]
                );

                foreach ($existedPermissions as $existedPermission) {
                    if (!in_array($existedPermission->getPermissionName(), $permissions)) {
                        $manager->remove($existedPermission);
                    }
                }

                foreach ($permissions as $permission) {
                    $permissionEntity = $manager->getRepository(Permission::class)->findOneBy(['name' => $permission]);
                    $planRolePermission = $manager->getRepository(PlanRolePermission::class)->findOneBy(
                        [
                            'plan' => $planEntity,
                            'role' => $roleEntity,
                            'permission' => $permissionEntity
                        ]
                    );
                    if (!$planRolePermission) {
                        $planRolePermission = new PlanRolePermission(
                            [
                                'plan' => $planEntity,
                                'role' => $roleEntity,
                                'permission' => $permissionEntity
                            ]
                        );
                        $manager->persist($planRolePermission);
                    }
                }
            }
        }

        foreach (self::ADMIN_ROLES_PERMISSIONS as $key => $value) {
            $permissionEntity = $manager->getRepository(Permission::class)->findOneBy(
                [
                    'name' => $key
                ]
            );
            foreach ($value as $role) {
                $roleEntity = $manager->getRepository(Role::class)->findOneBy(
                    [
                        'name' => $role,
                        'team' => Team::TEAM_ADMIN
                    ]
                );
                //remove deleted permissions for this type of user
                $existedPermissions = $manager->getRepository(PlanRolePermission::class)->findBy(
                    [
                        'plan' => null,
                        'role' => $roleEntity,
                    ]
                );

                foreach ($existedPermissions as $existedPermission) {
                    if (!isset(self::ADMIN_ROLES_PERMISSIONS[$existedPermission->getPermissionName()])
                        || !in_array($role, self::ADMIN_ROLES_PERMISSIONS[$existedPermission->getPermissionName()])) {
                        $manager->remove($existedPermission);
                    }
                }

                $planRolePermission = $manager->getRepository(PlanRolePermission::class)->findOneBy(
                    [
                        'plan' => null,
                        'role' => $roleEntity,
                        'permission' => $permissionEntity
                    ]
                );
                if (!$planRolePermission) {
                    $planRolePermission = new PlanRolePermission(
                        [
                            'plan' => null,
                            'role' => $roleEntity,
                            'permission' => $permissionEntity
                        ]
                    );
                    $manager->persist($planRolePermission);
                }
            }
        }

        foreach (self::RESELLER_ROLES_PERMISSIONS as $key => $value) {
            $permissionEntity = $manager->getRepository(Permission::class)->findOneBy(
                [
                    'name' => $key
                ]
            );
            foreach ($value as $role) {
                $roleEntity = $manager->getRepository(Role::class)->findOneBy(
                    [
                        'name' => $role,
                        'team' => Team::TEAM_RESELLER
                    ]
                );
                //remove deleted permissions for this type of user
                $existedPermissions = $manager->getRepository(PlanRolePermission::class)->findBy(
                    [
                        'plan' => null,
                        'role' => $roleEntity,
                    ]
                );

                foreach ($existedPermissions as $existedPermission) {
                    if (!isset(self::RESELLER_ROLES_PERMISSIONS[$existedPermission->getPermissionName()])
                        || !in_array(
                            $role,
                            self::RESELLER_ROLES_PERMISSIONS[$existedPermission->getPermissionName()]
                        )) {
                        $manager->remove($existedPermission);
                    }
                }

                $planRolePermission = $manager->getRepository(PlanRolePermission::class)->findOneBy(
                    [
                        'plan' => null,
                        'role' => $roleEntity,
                        'permission' => $permissionEntity
                    ]
                );
                if (!$planRolePermission) {
                    $planRolePermission = new PlanRolePermission(
                        [
                            'plan' => null,
                            'role' => $roleEntity,
                            'permission' => $permissionEntity
                        ]
                    );
                    $manager->persist($planRolePermission);
                }
            }
        }

        $manager->flush();
    }
}