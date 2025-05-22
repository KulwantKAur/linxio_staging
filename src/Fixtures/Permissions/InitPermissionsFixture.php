<?php

namespace App\Fixtures\Permissions;

use App\Entity\Permission;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;

class InitPermissionsFixture extends BaseFixture implements FixtureGroupInterface
{
    public const PERMISSIONS = [
        ['id' => 1, 'name' => Permission::CLIENT_LIST, 'displayName' => 'Client List'],
        ['id' => 2, 'name' => Permission::NEW_CLIENT, 'displayName' => 'New Client'],
        ['id' => 6, 'name' => Permission::CLIENT_NEW_USER, 'displayName' => 'Client New User'],
        ['id' => 7, 'name' => Permission::CLIENT_EDIT_USER, 'displayName' => 'Client Edit User'],
        ['id' => 8, 'name' => Permission::ADMIN_TEAM_USER_LIST, 'displayName' => 'Admin Team User List'],
        ['id' => 9, 'name' => Permission::ADMIN_TEAM_NEW_USER, 'displayName' => 'Admin Team New User'],
        ['id' => 10, 'name' => Permission::ADMIN_TEAM_EDIT_USER, 'displayName' => 'Admin Team Edit User'],
        ['id' => 11, 'name' => Permission::CLIENT_STATUS_HISTORY, 'displayName' => 'Client Status History'],
        ['id' => 12, 'name' => Permission::CLIENT_CREATED_HISTORY, 'displayName' => 'Client Created History'],
        ['id' => 13, 'name' => Permission::CLIENT_UPDATED_HISTORY, 'displayName' => 'Client Updated History'],
        ['id' => 14, 'name' => Permission::USER_HISTORY_CREATED, 'displayName' => 'User Created History'],
        ['id' => 15, 'name' => Permission::USER_HISTORY_UPDATED, 'displayName' => 'User Updated History'],
        ['id' => 16, 'name' => Permission::USER_HISTORY_LAST_LOGIN, 'displayName' => 'User Last Login History'],
        ['id' => 17, 'name' => Permission::ADMIN_TEAM_DELETE_USER, 'displayName' => 'Admin Team Delete User'],
        ['id' => 18, 'name' => Permission::DEVICE_NEW, 'displayName' => 'Client new device'],
        ['id' => 19, 'name' => Permission::DEVICE_LIST, 'displayName' => 'Client devices'],
        ['id' => 20, 'name' => Permission::DEVICE_EDIT, 'displayName' => 'Client edit device'],
        ['id' => 21, 'name' => Permission::DEVICE_DELETE, 'displayName' => 'Client delete device'],
        ['id' => 22, 'name' => Permission::VEHICLE_NEW, 'displayName' => 'New vehicle'],
        ['id' => 24, 'name' => Permission::VEHICLE_EDIT, 'displayName' => 'Edit vehicle'],
        ['id' => 25, 'name' => Permission::VEHICLE_DELETE, 'displayName' => 'Delete vehicle'],
        ['id' => 26, 'name' => Permission::VEHICLE_GROUP_NEW, 'displayName' => 'New vehicle group'],
        ['id' => 27, 'name' => Permission::VEHICLE_GROUP_LIST, 'displayName' => 'Vehicle group list'],
        ['id' => 28, 'name' => Permission::VEHICLE_GROUP_EDIT, 'displayName' => 'Edit vehicle group'],
        ['id' => 29, 'name' => Permission::VEHICLE_GROUP_DELETE, 'displayName' => 'Delete vehicle group'],
        ['id' => 30, 'name' => Permission::DEVICE_INSTALL_UNINSTALL, 'displayName' => 'Delete install/uninstall'],
        ['id' => 31, 'name' => Permission::SETTING_SET, 'displayName' => 'Set settings'],
        ['id' => 32, 'name' => Permission::DEPOT_NEW, 'displayName' => 'New depot'],
        ['id' => 33, 'name' => Permission::DEPOT_LIST, 'displayName' => 'Depots'],
        ['id' => 34, 'name' => Permission::DEPOT_EDIT, 'displayName' => 'Edit depot'],
        ['id' => 35, 'name' => Permission::DEPOT_DELETE, 'displayName' => 'Delete depot'],
        ['id' => 36, 'name' => Permission::VEHICLE_REMINDER_NEW, 'displayName' => 'New reminder'],
        ['id' => 37, 'name' => Permission::VEHICLE_REMINDER_LIST, 'displayName' => 'reminders'],
        ['id' => 38, 'name' => Permission::VEHICLE_REMINDER_EDIT, 'displayName' => 'Edit reminder'],
        ['id' => 39, 'name' => Permission::VEHICLE_REMINDER_DELETE, 'displayName' => 'Delete reminder'],
        ['id' => 40, 'name' => Permission::VEHICLE_SERVICE_RECORD_NEW, 'displayName' => 'New service record'],
        ['id' => 41, 'name' => Permission::VEHICLE_SERVICE_RECORD_LIST, 'displayName' => 'service records'],
        ['id' => 42, 'name' => Permission::VEHICLE_SERVICE_RECORD_EDIT, 'displayName' => 'Edit service record'],
        ['id' => 43, 'name' => Permission::VEHICLE_SERVICE_RECORD_DELETE, 'displayName' => 'Delete service record'],
        ['id' => 45, 'name' => Permission::CLIENT_DELETE_USER, 'displayName' => 'Client Delete User'],
        ['id' => 46, 'name' => Permission::VEHICLE_DOCUMENT_NEW, 'displayName' => 'New document'],
        ['id' => 47, 'name' => Permission::VEHICLE_DOCUMENT_EDIT, 'displayName' => 'Edit document'],
        ['id' => 48, 'name' => Permission::VEHICLE_DOCUMENT_LIST, 'displayName' => 'Documents list'],
        ['id' => 49, 'name' => Permission::VEHICLE_DOCUMENT_DELETE, 'displayName' => 'Delete document'],
        ['id' => 51, 'name' => Permission::AREA_NEW, 'displayName' => 'New area'],
        ['id' => 52, 'name' => Permission::AREA_EDIT, 'displayName' => 'Edit area'],
        ['id' => 53, 'name' => Permission::AREA_LIST, 'displayName' => 'Areas list'],
        ['id' => 54, 'name' => Permission::AREA_DELETE, 'displayName' => 'Delete area'],
        ['id' => 55, 'name' => Permission::CLIENT_NOTES_HISTORY, 'displayName' => 'Client notes history'],
        ['id' => 56, 'name' => Permission::DEVICE_CHANGE_TEAM, 'displayName' => 'Device change team'],
        ['id' => 57, 'name' => Permission::AREA_GROUP_NEW, 'displayName' => 'New area group'],
        ['id' => 58, 'name' => Permission::AREA_GROUP_LIST, 'displayName' => 'Area group list'],
        ['id' => 59, 'name' => Permission::AREA_GROUP_EDIT, 'displayName' => 'Edit area group'],
        ['id' => 60, 'name' => Permission::AREA_GROUP_DELETE, 'displayName' => 'Delete area group'],
        ['id' => 61, 'name' => Permission::LOGIN_AS_CLIENT, 'displayName' => 'Login as client'],
        ['id' => 62, 'name' => Permission::LOGIN_AS_USER, 'displayName' => 'Login as user'],
        ['id' => 63, 'name' => Permission::FULL_SEARCH, 'displayName' => 'Full search'],
        ['id' => 64, 'name' => Permission::DRIVER_LIST, 'displayName' => 'Driver list'],
        ['id' => 65, 'name' => Permission::FUEL_IGNORE_NEW, 'displayName' => 'New fuel ignore'],
        ['id' => 66, 'name' => Permission::FUEL_IGNORE_EDIT, 'displayName' => 'Edit fuel ignore'],
        ['id' => 67, 'name' => Permission::FUEL_IGNORE_LIST, 'displayName' => 'Fuel ignore list'],
        ['id' => 68, 'name' => Permission::FUEL_IGNORE_DELETE, 'displayName' => 'Delete fuel ignore'],
        ['id' => 69, 'name' => Permission::FUEL_MAPPING_NEW, 'displayName' => 'New fuel mapping'],
        ['id' => 70, 'name' => Permission::FUEL_MAPPING_EDIT, 'displayName' => 'Edit fuel mapping'],
        ['id' => 71, 'name' => Permission::FUEL_MAPPING_LIST, 'displayName' => 'Fuel mapping list'],
        ['id' => 72, 'name' => Permission::FUEL_MAPPING_DELETE, 'displayName' => 'Delete fuel mapping'],
        ['id' => 73, 'name' => Permission::MAP_VIEW, 'displayName' => 'Map view'],
        ['id' => 74, 'name' => Permission::REMINDER_CATEGORY_LIST, 'displayName' => 'Reminder Category list'],
        ['id' => 75, 'name' => Permission::REMINDER_CATEGORY_NEW, 'displayName' => 'New Reminder Category'],
        ['id' => 76, 'name' => Permission::REMINDER_CATEGORY_EDIT, 'displayName' => 'Edit Reminder Category'],
        ['id' => 77, 'name' => Permission::REMINDER_CATEGORY_DELETE, 'displayName' => 'Delete Reminder Category'],
        ['id' => 80, 'name' => Permission::MAP_SECTION_VEHICLE, 'displayName' => 'Map section vehicle'],
        ['id' => 81, 'name' => Permission::MAP_SECTION_DRIVERS, 'displayName' => 'Map section drivers'],
        ['id' => 82, 'name' => Permission::MAP_SECTION_GEOFENCES, 'displayName' => 'Map section geofences'],
        ['id' => 83, 'name' => Permission::FLEET_SECTION_DASHBOARD, 'displayName' => 'Fleet section dashboard'],
        ['id' => 84, 'name' => Permission::FLEET_SECTION_FLEET, 'displayName' => 'Fleet section fleet'],
        [
            'id' => 85,
            'name' => Permission::FLEET_SECTION_SERVICE_REMINDERS,
            'displayName' => 'Fleet section service reminders'
        ],
        ['id' => 86, 'name' => Permission::FLEET_SECTION_DOCUMENTS, 'displayName' => 'Fleet section documents'],
        ['id' => 93, 'name' => Permission::FLEET_SECTION_ADD_VEHICLE, 'displayName' => 'Fleet section add vehicles'],
        ['id' => 95, 'name' => Permission::FUEL_SUMMARY, 'displayName' => 'Fuel summary'],
        ['id' => 96, 'name' => Permission::FUEL_RECORDS, 'displayName' => 'Fuel records'],
        ['id' => 97, 'name' => Permission::FUEL_IMPORT_DATA, 'displayName' => 'Fuel import data'],
        ['id' => 98, 'name' => Permission::DRIVERS_SECTION, 'displayName' => 'Drivers section'],
        ['id' => 99, 'name' => Permission::DRIVERS_EDIT_PROFILE_INFO, 'displayName' => 'Drivers edit profile info'],
        ['id' => 101, 'name' => Permission::DRIVERS_ADD_DRIVER, 'displayName' => 'Drivers add driver'],
        [
            'id' => 102,
            'name' => Permission::DRIVING_BEHAVIOUR_DASHBOARD,
            'displayName' => 'Driving behaviour dashboard'
        ],
        ['id' => 103, 'name' => Permission::DRIVING_BEHAVIOUR_VEHICLES, 'displayName' => 'Driving behaviour vehicles'],
        ['id' => 104, 'name' => Permission::DRIVING_BEHAVIOUR_DRIVERS, 'displayName' => 'Driving behaviour drivers'],
        ['id' => 105, 'name' => Permission::ALERTS_SECTION, 'displayName' => 'Alerts section'],
        ['id' => 116, 'name' => Permission::DEVICES_EDIT_RAW_DATA_LOG, 'displayName' => 'Devices edit raw data log'],
        ['id' => 117, 'name' => Permission::DEVICES_REGISTER_DEVICE, 'displayName' => 'Devices register device'],
        ['id' => 118, 'name' => Permission::CONFIGURATION_COMPANY_INFO, 'displayName' => 'Configuration company info'],
        [
            'id' => 119,
            'name' => Permission::CONFIGURATION_DRIVING_OPTIONS,
            'displayName' => 'Configuration driving options'
        ],
        ['id' => 120, 'name' => Permission::CONFIGURATION_FLEET, 'displayName' => 'Configuration fleet'],
        [
            'id' => 121,
            'name' => Permission::CONFIGURATION_NOTIFICATIONS,
            'displayName' => 'Configuration notifications'
        ],
        ['id' => 122, 'name' => Permission::CONFIGURATION_USERS, 'displayName' => 'Configuration users'],
        ['id' => 123, 'name' => Permission::CONFIGURATION_GEOFENCES, 'displayName' => 'Configurations geofences'],
        ['id' => 124, 'name' => Permission::SUPPORT_SECTION, 'displayName' => 'Support section'],
        ['id' => 125, 'name' => Permission::FUEL_FILE_UPDATE, 'displayName' => 'Fuel file update'],
        ['id' => 126, 'name' => Permission::FUEL_FILE_DELETE, 'displayName' => 'Fuel file delete'],
        ['id' => 127, 'name' => Permission::MAP_SECTION, 'displayName' => 'Map section'],
        ['id' => 128, 'name' => Permission::FLEET_SECTION, 'displayName' => 'Fleet section'],
        ['id' => 129, 'name' => Permission::FUEL_SECTION, 'displayName' => 'Fuel section'],
        ['id' => 130, 'name' => Permission::DRIVING_BEHAVIOUR_SECTION, 'displayName' => 'Driving behaviour section'],
        ['id' => 131, 'name' => Permission::REPORTS_SECTION, 'displayName' => 'Reports section'],
        ['id' => 132, 'name' => Permission::CONFIGURATION_SECTION, 'displayName' => 'Configuration section'],
        ['id' => 133, 'name' => Permission::INSPECTION_FORM_FILL, 'displayName' => 'Fill Inspection form'],
        ['id' => 135, 'name' => Permission::INSPECTION_FORM_FILLED, 'displayName' => 'Get filled inspection form'],
        ['id' => 136, 'name' => Permission::REPAIR_COST_NEW, 'displayName' => 'New repair cost'],
        ['id' => 137, 'name' => Permission::REPAIR_COST_LIST, 'displayName' => 'Repair cost list'],
        ['id' => 138, 'name' => Permission::REPAIR_COST_EDIT, 'displayName' => 'Edit repair cost'],
        ['id' => 139, 'name' => Permission::REPAIR_COST_DELETE, 'displayName' => 'Delete repair cost'],
        ['id' => 140, 'name' => Permission::NOTIFICATION_LIST, 'displayName' => 'Notifications'],
        ['id' => 141, 'name' => Permission::NOTIFICATION_NEW, 'displayName' => 'New notification'],
        ['id' => 142, 'name' => Permission::NOTIFICATION_EDIT, 'displayName' => 'Edit notification'],
        ['id' => 143, 'name' => Permission::NOTIFICATION_DELETE, 'displayName' => 'Delete notification'],
        ['id' => 144, 'name' => Permission::CLIENT_SECTION, 'displayName' => 'Client section'],
        ['id' => 145, 'name' => Permission::ADMIN_TEAM_SECTION, 'displayName' => 'Admin team section'],
        [
            'id' => 149,
            'name' => Permission::DEVICES_VEHICLES_IMPORT_DATA,
            'displayName' => 'Import devices/vehicles data'
        ],
        ['id' => 150, 'name' => Permission::SET_MOBILE_DEVICE, 'displayName' => 'Set mobile device'],
        ['id' => 151, 'name' => Permission::LOGIN_WITH_ID, 'displayName' => 'Log in with ID'],
        ['id' => 153, 'name' => Permission::USER_GROUP_NEW, 'displayName' => 'New User group'],
        ['id' => 154, 'name' => Permission::USER_GROUP_EDIT, 'displayName' => 'Edit User group'],
        ['id' => 155, 'name' => Permission::USER_GROUP_DELETE, 'displayName' => 'Delete user group'],
        ['id' => 156, 'name' => Permission::FUEL_TYPES_LIST, 'displayName' => 'Fuel types list'],
        ['id' => 157, 'name' => Permission::CONFIGURATION_USER_GROUPS, 'displayName' => 'Configuration user groups'],
        ['id' => 158, 'name' => Permission::SET_MOBILE_DEVICE_TOKEN, 'displayName' => 'Set mobile device token'],
        ['id' => 160, 'name' => Permission::INSPECTION_FORM_SET_TEAM, 'displayName' => 'Inspection form set team'],
        ['id' => 161, 'name' => Permission::TRACK_LINK_CREATE, 'displayName' => 'Create track link'],
        ['id' => 162, 'name' => Permission::CONFIGURATION_TEMPLATES, 'displayName' => 'Configuration templates'],
        ['id' => 163, 'name' => Permission::DIGITAL_FORM_LIST, 'displayName' => 'Digital form list'],
        ['id' => 164, 'name' => Permission::DIGITAL_FORM_VIEW, 'displayName' => 'Digital form view'],
        ['id' => 168, 'name' => Permission::DIGITAL_FORM_ANSWER_VIEW, 'displayName' => 'Digital form answer view'],
        ['id' => 169, 'name' => Permission::DIGITAL_FORM_ANSWER_CREATE, 'displayName' => 'Digital form answer create'],
        [
            'id' => 173,
            'name' => Permission::VEHICLE_INSPECTION_FORM_LIST,
            'displayName' => 'Vehicle inspection plan list'
        ],
        [
            'id' => 174,
            'name' => Permission::VEHICLE_INSPECTION_FORM_CREATE,
            'displayName' => 'Vehicle inspection plan create'
        ],
        [
            'id' => 175,
            'name' => Permission::VEHICLE_INSPECTION_FORM_EDIT,
            'displayName' => 'Vehicle inspection plan edit'
        ],
        ['id' => 176, 'name' => Permission::DEVICE_SENSOR_LIST, 'displayName' => 'Device sensor list'],
        ['id' => 178, 'name' => Permission::DEVICE_SENSOR_CREATE, 'displayName' => 'Device sensor create'],
        ['id' => 179, 'name' => Permission::DEVICE_SENSOR_EDIT, 'displayName' => 'Device sensor edit'],
        ['id' => 180, 'name' => Permission::DEVICE_SENSOR_DELETE, 'displayName' => 'Device sensor delete'],
        ['id' => 185, 'name' => Permission::SCHEDULED_REPORT_LIST, 'displayName' => 'Scheduled report list'],
        ['id' => 186, 'name' => Permission::SCHEDULED_REPORT_CREATE, 'displayName' => 'Scheduled report create'],
        ['id' => 187, 'name' => Permission::SCHEDULED_REPORT_EDIT, 'displayName' => 'Scheduled report edit'],
        ['id' => 188, 'name' => Permission::SCHEDULED_REPORT_DELETE, 'displayName' => 'Scheduled report delete'],
        ['id' => 190, 'name' => Permission::ASSET_NEW, 'displayName' => 'New asset'],
        ['id' => 191, 'name' => Permission::ASSET_EDIT, 'displayName' => 'Edit asset'],
        ['id' => 192, 'name' => Permission::ASSET_DELETE, 'displayName' => 'Delete asset'],
        ['id' => 193, 'name' => Permission::ASSET_LIST, 'displayName' => 'Asset list'],
        ['id' => 194, 'name' => Permission::ASSET_INSTALL_UNINSTALL, 'displayName' => 'Asset install/uninstall'],
        ['id' => 195, 'name' => Permission::ASSET_DASHBOARD, 'displayName' => 'Asset dashboard'],
        ['id' => 196, 'name' => Permission::ASSET_REMINDER_LIST, 'displayName' => 'Asset service reminders'],
        ['id' => 197, 'name' => Permission::ASSET_DOCUMENT_LIST, 'displayName' => 'Asset documents'],
        ['id' => 198, 'name' => Permission::ASSET_SECTION, 'displayName' => 'Asset section'],
        [
            'id' => 199,
            'name' => Permission::ASSET_SECTION_SERVICE_REMINDERS,
            'displayName' => 'Asset section service reminders'
        ],
        ['id' => 200, 'name' => Permission::ASSET_SECTION_DOCUMENTS, 'displayName' => 'Asset section document'],
        [
            'id' => 201,
            'name' => Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
            'displayName' => 'Asset section edit service reminders'
        ],
        [
            'id' => 202,
            'name' => Permission::ASSET_SECTION_EDIT_DOCUMENTS,
            'displayName' => 'Asset section edit documents'
        ],
        [
            'id' => 203,
            'name' => Permission::ASSET_SECTION_EDIT_REPAIR_COSTS,
            'displayName' => 'Asset section edit repair costs'
        ],
        ['id' => 204, 'name' => Permission::RESELLER_LIST, 'displayName' => 'Reseller list'],
        ['id' => 205, 'name' => Permission::RESELLER_NEW, 'displayName' => 'Reseller create'],
        ['id' => 206, 'name' => Permission::RESELLER_EDIT, 'displayName' => 'Reseller edit'],
        ['id' => 207, 'name' => Permission::RESELLER_DELETE, 'displayName' => 'Reseller delete'],
        ['id' => 208, 'name' => Permission::RESELLER_USER_NEW, 'displayName' => 'Reseller user create'],
        ['id' => 209, 'name' => Permission::RESELLER_USER_EDIT, 'displayName' => 'Reseller user edit'],
        ['id' => 210, 'name' => Permission::RESELLER_USER_DELETE, 'displayName' => 'Reseller user delete'],
        ['id' => 211, 'name' => Permission::RESELLER_USER_LIST, 'displayName' => 'Reseller user list'],
        ['id' => 212, 'name' => Permission::RESELLER_SECTION, 'displayName' => 'Reseller section'],
        ['id' => 213, 'name' => Permission::LOGIN_AS_RESELLER, 'displayName' => 'Login as reseller'],
        ['id' => 214, 'name' => Permission::RESELLER_TEAM_SECTION, 'displayName' => 'Reseller team section'],
        ['id' => 215, 'name' => Permission::RESELLER_NOTES_HISTORY, 'displayName' => 'Reseller notes history'],
        [
            'id' => 216,
            'name' => Permission::PLATFORM_SETTING_ADMIN_EDIT,
            'displayName' => 'Admin Platform setting edit'
        ],
        [
            'id' => 217,
            'name' => Permission::PLATFORM_SETTING_RESELLER_EDIT,
            'displayName' => 'Reseller Platform setting edit'
        ],
        ['id' => 218, 'name' => Permission::MAP_SECTION_ASSETS, 'displayName' => 'Map section Asset'],
        ['id' => 219, 'name' => Permission::VEHICLE_DOCUMENT_RECORD_NEW, 'displayName' => 'New document record'],
        ['id' => 220, 'name' => Permission::DRIVER_BLOCK_USER, 'displayName' => 'Block driver'],
        ['id' => 221, 'name' => Permission::DRIVER_RESET_PASSWORD, 'displayName' => 'Driver reset password'],
        ['id' => 222, 'name' => Permission::DRIVER_DELETE, 'displayName' => 'Delete driver'],
        ['id' => 223, 'name' => Permission::DRIVER_DOCUMENT_LIST, 'displayName' => 'Driver documents'],
        ['id' => 224, 'name' => Permission::DRIVER_DOCUMENT_NEW, 'displayName' => 'Create driver document'],
        ['id' => 225, 'name' => Permission::DRIVER_DOCUMENT_EDIT, 'displayName' => 'Edit driver document'],
        ['id' => 226, 'name' => Permission::DRIVER_DOCUMENT_RECORD_LIST, 'displayName' => 'Driver document records'],
        ['id' => 227, 'name' => Permission::DRIVER_DOCUMENT_DELETE, 'displayName' => 'Delete driver document'],
        ['id' => 228, 'name' => Permission::VEHICLE_INSPECTION_FORM_DELETE, 'displayName' => 'Delete Inspection Plan'],
        ['id' => 229, 'name' => Permission::SUPPORT_SUBMIT_TICKET, 'displayName' => 'Submit support ticket'],
        ['id' => 230, 'name' => Permission::DEVICE, 'displayName' => 'Device'],
        ['id' => 231, 'name' => Permission::VEHICLE_DOCUMENT_RECORD_LIST, 'displayName' => 'Document record list'],
        ['id' => 232, 'name' => Permission::ASSET_REMINDER_NEW, 'displayName' => 'New asset service reminder'],
        ['id' => 233, 'name' => Permission::ASSET_REMINDER_EDIT, 'displayName' => 'Edit  asset service reminder'],
        ['id' => 234, 'name' => Permission::ASSET_REMINDER_DELETE, 'displayName' => 'Delete asset service reminder'],
        ['id' => 235, 'name' => Permission::ASSET_SERVICE_RECORD_LIST, 'displayName' => 'Asset service reminder records'],
        ['id' => 236, 'name' => Permission::ASSET_SERVICE_RECORD_NEW, 'displayName' => 'New asset service reminder record'],
        ['id' => 237, 'name' => Permission::ASSET_SERVICE_RECORD_EDIT, 'displayName' => 'Edit  asset service reminder record'],
        ['id' => 238, 'name' => Permission::ASSET_SERVICE_RECORD_DELETE, 'displayName' => 'Delete asset service reminder record'],
        ['id' => 239, 'name' => Permission::ASSET_DOCUMENT_NEW, 'displayName' => 'New asset document'],
        ['id' => 240, 'name' => Permission::ASSET_DOCUMENT_EDIT, 'displayName' => 'Edit  asset document'],
        ['id' => 241, 'name' => Permission::ASSET_DOCUMENT_DELETE, 'displayName' => 'Delete  asset document'],
        ['id' => 242, 'name' => Permission::ASSET_DOCUMENT_RECORD_LIST, 'displayName' => 'Asset document records'],
        ['id' => 243, 'name' => Permission::ASSET_DOCUMENT_RECORD_NEW, 'displayName' => 'New asset document record'],
        ['id' => 244, 'name' => Permission::DRIVER_DOCUMENT_RECORD_NEW, 'displayName' => 'New driver document record'],
        ['id' => 245, 'name' => Permission::CONFIGURATION_COMPANY_INFO_EDIT, 'displayName' => 'Edit company info'],
        ['id' => 246, 'name' => Permission::CONFIGURATION_DRIVING_OPTIONS_EDIT, 'displayName' => 'Edit driving options'],
        ['id' => 247, 'name' => Permission::CLIENT_BLOCK_USER, 'displayName' => 'Block client user'],
        ['id' => 248, 'name' => Permission::CLIENT_USER_RESET_PWD, 'displayName' => 'Reset client user password'],
        ['id' => 249, 'name' => Permission::CONFIGURATION_TEMPLATES_EDIT, 'displayName' => 'Edit company templates'],
        ['id' => 250, 'name' => Permission::CONFIGURATION_INTEGRATIONS_LIST, 'displayName' => 'Edit company templates'],
        ['id' => 251, 'name' => Permission::CONFIGURATION_USER_GROUP_LIST_VEHICLES, 'displayName' => 'User group list vehicles'],
        ['id' => 252, 'name' => Permission::CONFIGURATION_USER_GROUP_LIST_AREAS, 'displayName' => 'User group list areas'],
        ['id' => 253, 'name' => Permission::CONFIGURATION_USER_GROUP_LIST_MODULES, 'displayName' => 'User group list modules'],
        ['id' => 254, 'name' => Permission::SUPPORT_CONTACT_US, 'displayName' => 'Support contact us'],
        ['id' => 255, 'name' => Permission::CHAT_LIST, 'displayName' => 'Chat list'],
        ['id' => 256, 'name' => Permission::CHAT_CREATE, 'displayName' => 'Chat create'],
        ['id' => 263, 'name' => Permission::CHAT_LIST_ALL, 'displayName' => 'Chat list all'],
        ['id' => 264, 'name' => Permission::VEHICLE_ARCHIVE, 'displayName' => 'Archive vehicle'],
        ['id' => 265, 'name' => Permission::BILLING_PLAN_EDIT, 'displayName' => 'Edit Billing Plan'],
        ['id' => 266, 'name' => Permission::BILLING, 'displayName' => 'Billing'],
        ['id' => 267, 'name' => Permission::CLIENT_ARCHIVE_USER, 'displayName' => 'Client archive user'],
        ['id' => 268, 'name' => Permission::AREA_ARCHIVE, 'displayName' => 'Area archive'],
        ['id' => 269, 'name' => Permission::VEHICLE_DOCUMENT_ARCHIVE, 'displayName' => 'Vehicle document archive'],
        ['id' => 270, 'name' => Permission::DRIVER_DOCUMENT_ARCHIVE, 'displayName' => 'Driver document archive'],
        ['id' => 271, 'name' => Permission::ASSET_DOCUMENT_ARCHIVE, 'displayName' => 'Asset document archive'],
        ['id' => 272, 'name' => Permission::VEHICLE_REMINDER_ARCHIVE, 'displayName' => 'Vehicle reminder archive'],
        ['id' => 273, 'name' => Permission::ASSET_REMINDER_ARCHIVE, 'displayName' => 'Asset reminder archive'],
        ['id' => 274, 'name' => Permission::DEPOT_ARCHIVE, 'displayName' => 'Depot archive'],
        ['id' => 275, 'name' => Permission::AREA_GROUP_ARCHIVE, 'displayName' => 'Area group archive'],
        ['id' => 276, 'name' => Permission::USER_GROUP_ARCHIVE, 'displayName' => 'User group archive'],
        ['id' => 277, 'name' => Permission::VEHICLE_GROUP_ARCHIVE, 'displayName' => 'Vehicle group archive'],
        ['id' => 278, 'name' => Permission::DRIVER_ARCHIVE, 'displayName' => 'Archive driver'],
        ['id' => 279, 'name' => Permission::MOVE_DEVICE, 'displayName' => 'Move device'],
        ['id' => 280, 'name' => Permission::XERO_API, 'displayName' => 'Xero API'],
        ['id' => 281, 'name' => Permission::BILLING_INVOICE_VIEW, 'displayName' => 'View invoice'],
        ['id' => 282, 'name' => Permission::BILLING_INVOICE_PAY, 'displayName' => 'Pay invoice'],
        ['id' => 283, 'name' => Permission::BILLING_PAYMENT_CHANGE, 'displayName' => 'Manage payment methods'],
        ['id' => 284, 'name' => Permission::STRIPE_API, 'displayName' => 'Stripe API'],
        ['id' => 285, 'name' => Permission::BILLING_ADMIN, 'displayName' => 'Billing admin'],
        ['id' => 286, 'name' => Permission::FUEL_STATION_CREATE, 'displayName' => 'Create fuel station'],
        ['id' => 287, 'name' => Permission::FUEL_STATION_EDIT, 'displayName' => 'Edit fuel station'],
        ['id' => 288, 'name' => Permission::FUEL_STATION_DELETE, 'displayName' => 'Delete fuel station'],
        ['id' => 289, 'name' => Permission::FUEL_STATION_LIST, 'displayName' => 'Fuel station list'],
        ['id' => 290, 'name' => Permission::FUEL_RECORD_UPDATE, 'displayName' => 'Update fuel record'],
        ['id' => 291, 'name' => Permission::BILLING_INVOICE_CLEAN, 'displayName' => 'Clean not synced invoices'],
        ['id' => 292, 'name' => Permission::CAMERAS, 'displayName' => 'Cameras'],
    ];

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        foreach (self::PERMISSIONS as $permissionData) {
            $permission = $manager->getRepository(Permission::class)->find($permissionData['id']);
            if (!$permission) {
                $permission = new Permission($permissionData);
                $permission->setId($permissionData['id']);
                $manager->persist($permission);
            } else {
                $permission->setName($permissionData['name']);
                $permission->setDisplayName($permissionData['displayName']);
            }
        }
        $manager->flush();

        $this->removeOldPermissions($manager);
    }

    public function removeOldPermissions(EntityManager $manager)
    {
        $permissions = $manager->getRepository(Permission::class)->findAll();
        foreach ($permissions as $permission) {
            $isset = array_search($permission->getId(), array_column(self::PERMISSIONS, 'id'));
            if ($isset === false) {
                $manager->remove($permission);
            }
        }
        $manager->flush();
    }
}