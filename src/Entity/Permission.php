<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 */
#[ORM\Table(name: 'permission')]
#[ORM\Entity(repositoryClass: 'App\Repository\PermissionRepository')]
class Permission extends BaseEntity
{
    public const CLIENT_LIST = 'client_list';
    public const NEW_CLIENT = 'new_client';
    public const CLIENT_NEW_USER = 'configuration.users_list.add';
    public const CLIENT_BLOCK_USER = 'configuration.users_list.block_user';
    public const CLIENT_USER_RESET_PWD = 'configuration.users_list.reset_password';
    public const CLIENT_EDIT_USER = 'configuration.users_list.edit';
    public const CLIENT_ARCHIVE_USER = 'configuration.users_list.archive';
    public const CLIENT_DELETE_USER = 'configuration.users_list.delete';
    public const CLIENT_STATUS_HISTORY = 'configuration.company_info.status_history';
    public const CLIENT_CREATED_HISTORY = 'client_created_history';
    public const CLIENT_UPDATED_HISTORY = 'client_updated_history';
    public const CLIENT_NOTES_HISTORY = 'configuration.company_info.notes_history';
    public const ADMIN_TEAM_USER_LIST = 'admin_team_user_list';
    public const ADMIN_TEAM_NEW_USER = 'admin_team_new_user';
    public const ADMIN_TEAM_EDIT_USER = 'admin_team_edit_user';
    public const ADMIN_TEAM_DELETE_USER = 'admin_team_delete_user';
    public const USER_HISTORY_CREATED = 'user_history_created';
    public const USER_HISTORY_UPDATED = 'user_history_updated';
    public const USER_HISTORY_LAST_LOGIN = 'user_history_last_login';
    public const DEVICE_LIST = 'device.devices_list';
    public const DEVICE_BY_ID = 'device_by_id';
    public const DEVICE_NEW = 'device_new';
    public const DEVICE_EDIT = 'device.devices_list.edit';
    public const DEVICE_DELETE = 'device_delete';
    public const DEVICE_INSTALL_UNINSTALL = 'device.devices_list.install';
    public const DEVICE_CHANGE_TEAM = 'device_change_team';
    public const VEHICLE_NEW = 'vehicle_new';
    public const VEHICLE_EDIT = 'fleet.vehicle_list.edit';
    public const VEHICLE_DELETE = 'fleet.vehicle_list.delete';
    public const VEHICLE_ARCHIVE = 'fleet.vehicle_list.archive';
    public const VEHICLE_GROUP_LIST_MOBILE = 'vehicle_group_list';
    public const VEHICLE_GROUP_LIST = 'configuration.fleet.vehicle_groups_list';
    public const VEHICLE_GROUP_NEW = 'configuration.fleet.vehicle_groups_list.add';
    public const VEHICLE_GROUP_EDIT = 'configuration.fleet.vehicle_groups_list.edit';
    public const VEHICLE_GROUP_DELETE = 'configuration.fleet.vehicle_groups_list.delete';
    public const VEHICLE_GROUP_ARCHIVE = 'configuration.fleet.vehicle_groups_list.archive';
    public const SETTING_SET = 'setting_set';
    public const DEPOT_LIST_MOBILE = 'depot_list';
    public const DEPOT_LIST = 'configuration.fleet.depot_list';
    public const DEPOT_NEW = 'configuration.fleet.depot_list.add';
    public const DEPOT_EDIT = 'configuration.fleet.depot_list.edit';
    public const DEPOT_DELETE = 'configuration.fleet.depot_list.delete';
    public const DEPOT_ARCHIVE = 'configuration.fleet.depot_list.archive';
    public const VEHICLE_REMINDER_LIST = 'fleet.service_reminders_list';
    public const VEHICLE_REMINDER_NEW = 'fleet.service_reminders_list.add';
    public const VEHICLE_REMINDER_EDIT = 'fleet.service_reminders_list.edit';
    public const VEHICLE_REMINDER_DELETE = 'fleet.service_reminders_list.delete';
    public const VEHICLE_REMINDER_ARCHIVE = 'fleet.service_reminders_list.archive';
    public const VEHICLE_SERVICE_RECORD_LIST = 'fleet.service_reminders_list.service_record_list';
    public const VEHICLE_SERVICE_RECORD_NEW = 'fleet.service_reminders_list.service_record_list.add'; //validate
    public const VEHICLE_SERVICE_RECORD_EDIT = 'fleet.service_reminders_list.service_record_list.edit';
    public const VEHICLE_SERVICE_RECORD_DELETE = 'fleet.service_reminders_list.service_record_list.delete';
    public const VEHICLE_DOCUMENT_LIST = 'fleet.documents_list';
    public const VEHICLE_DOCUMENT_NEW = 'fleet.documents_list.add';
    public const VEHICLE_DOCUMENT_EDIT = 'fleet.documents_list.edit';
    public const VEHICLE_DOCUMENT_DELETE = 'fleet.documents_list.delete';
    public const VEHICLE_DOCUMENT_ARCHIVE = 'fleet.documents_list.archive';
    public const VEHICLE_DOCUMENT_RECORD_NEW = 'fleet.documents_list.records_list.add';
    public const VEHICLE_DOCUMENT_RECORD_LIST = 'fleet.documents_list.records_list';
    public const AREA_LIST = 'configuration.areas.areas_list';
    public const AREA_NEW = 'configuration.areas.areas_list.add';
    public const AREA_EDIT = 'configuration.areas.areas_list.edit';
    public const AREA_ARCHIVE = 'configuration.areas.areas_list.archive';
    public const AREA_DELETE = 'configuration.areas.areas_list.delete';
    public const AREA_GROUP_LIST = 'configuration.areas.areas_groups_list';
    public const AREA_GROUP_NEW = 'configuration.areas.areas_groups_list.add';
    public const AREA_GROUP_EDIT = 'configuration.areas.areas_groups_list.edit';
    public const AREA_GROUP_DELETE = 'configuration.areas.areas_groups_list.delete';
    public const AREA_GROUP_ARCHIVE = 'configuration.areas.areas_groups_list.archive';
    public const LOGIN_AS_CLIENT = 'login_as_client';
    public const LOGIN_AS_USER = 'login_as_user';
    public const FULL_SEARCH = 'full_search';
    public const DRIVER_LIST = 'drivers.drivers_list';
    public const FUEL_IGNORE_LIST = 'fuel_ignore_list';
    public const FUEL_IGNORE_NEW = 'fuel_ignore_new';
    public const FUEL_IGNORE_EDIT = 'fuel_ignore_edit';
    public const FUEL_IGNORE_DELETE = 'fuel_ignore_delete';
    public const FUEL_MAPPING_LIST = 'fuel_mapping_list';
    public const FUEL_TYPES_LIST = 'fuel_types_list';
    public const FUEL_MAPPING_NEW = 'fuel_mapping_new';
    public const FUEL_MAPPING_EDIT = 'fuel_mapping_edit';
    public const FUEL_MAPPING_DELETE = 'fuel_mapping_delete';
    public const MAP_VIEW = 'map_view';
    public const REMINDER_CATEGORY_LIST = 'reminder_category_list';
    public const REMINDER_CATEGORY_NEW = 'reminder_category_new';
    public const REMINDER_CATEGORY_EDIT = 'reminder_category_edit';
    public const REMINDER_CATEGORY_DELETE = 'reminder_category_delete';

    public const MAP_SECTION = 'map';
    public const MAP_SECTION_VEHICLE = 'map.vehicle';
    public const MAP_SECTION_DRIVERS = 'map.drivers';
    public const MAP_SECTION_GEOFENCES = 'map.areas';
    public const MAP_SECTION_GEOFENCES_MOBILE = 'map_section_geofences';
    public const MAP_SECTION_ASSETS = 'map.assets';

    public const FLEET_SECTION = 'fleet';
    public const FLEET_SECTION_DASHBOARD = 'fleet.dashboard';
    public const FLEET_SECTION_FLEET = 'fleet.vehicle_list';
    public const FLEET_SECTION_SERVICE_REMINDERS = 'fleet_section_service_reminders';
    public const FLEET_SECTION_DOCUMENTS = 'fleet_section_documents';
    public const FLEET_SECTION_ADD_VEHICLE = 'fleet.vehicle_list.add';

    public const FUEL_SECTION = 'fuel';
    public const FUEL_SUMMARY = 'fuel.summary';
    public const FUEL_RECORDS = 'fuel.records';
    public const FUEL_IMPORT_DATA = 'fuel.import_data';
    public const FUEL_FILE_UPDATE = 'fuel_file_update';
    public const FUEL_FILE_DELETE = 'fuel_file_delete';
    public const FUEL_RECORD_UPDATE = 'fuel.fuel_record_update';

    public const DRIVERS_SECTION = 'drivers';
    public const DRIVERS_EDIT_PROFILE_INFO = 'drivers.drivers_list.edit';
    public const DRIVERS_ADD_DRIVER = 'drivers.drivers_list.add';

    public const DRIVING_BEHAVIOUR_SECTION = 'driving_behaviour';
    public const DRIVING_BEHAVIOUR_SECTION_MOBILE = 'driving_behaviour_section';
    public const DRIVING_BEHAVIOUR_DASHBOARD = 'driving_behaviour.dashboard';
    public const DRIVING_BEHAVIOUR_DASHBOARD_MOBILE = 'driving_behaviour_dashboard';
    public const DRIVING_BEHAVIOUR_VEHICLES = 'driving_behaviour.vehicles';
    public const DRIVING_BEHAVIOUR_VEHICLES_MOBILE = 'driving_behaviour_vehicles';
    public const DRIVING_BEHAVIOUR_DRIVERS = 'driving_behaviour.drivers';
    public const DRIVING_BEHAVIOUR_DRIVERS_MOBILE = 'driving_behaviour_drivers';

    public const ALERTS_SECTION = 'alerts';

    public const REPORTS_SECTION = 'reports';

    public const DEVICE = 'device';
    public const DEVICES_EDIT_RAW_DATA_LOG = 'device.devices_list.raw_data_log';
    public const DEVICES_REGISTER_DEVICE = 'devices_register_device';

    public const CONFIGURATION_SECTION = 'configuration';
    public const CONFIGURATION_COMPANY_INFO = 'configuration.company_info';
    public const CONFIGURATION_COMPANY_INFO_EDIT = 'configuration.company_info.edit';
    public const CONFIGURATION_DRIVING_OPTIONS = 'configuration.driving_options';
    public const CONFIGURATION_DRIVING_OPTIONS_EDIT = 'configuration.driving_options.edit';
    public const CONFIGURATION_FLEET = 'configuration.fleet';
    public const CONFIGURATION_NOTIFICATIONS = 'configuration.notifications_list';
    public const CONFIGURATION_USERS = 'configuration.users_list';
    public const CONFIGURATION_GEOFENCES = 'configuration.areas';
    public const CONFIGURATION_USER_GROUPS = 'configuration.user_group_list';
    public const CONFIGURATION_TEMPLATES = 'configuration.templates';
    public const CONFIGURATION_TEMPLATES_EDIT = 'configuration.templates.edit';

    public const SUPPORT_SECTION = 'support';

    public const INSPECTION_FORM_FILL = 'inspection_form_fill';
    public const INSPECTION_FORM_SET_TEAM = 'inspection_form_set_team';

    public const INSPECTION_FORM_FILLED = 'inspection_form_filled';

    public const REPAIR_COST_LIST = 'fleet.repair_costs_list';
    public const REPAIR_COST_LIST_MOBILE = 'repair_cost_list';
    public const REPAIR_COST_NEW = 'fleet.repair_costs_list.add';
    public const REPAIR_COST_NEW_MOBILE = 'repair_cost_new';
    public const REPAIR_COST_EDIT = 'fleet.repair_costs_list.edit';
    public const REPAIR_COST_EDIT_MOBILE = 'repair_cost_edit';
    public const REPAIR_COST_DELETE = 'fleet.repair_costs_list.delete';

    public const NOTIFICATION_LIST = 'configuration.notifications_list';
    public const NOTIFICATION_NEW = 'configuration.notifications_list.add';
    public const NOTIFICATION_DELETE = 'configuration.notifications_list.delete';
    public const NOTIFICATION_EDIT = 'configuration.notifications_list.edit';

    public const CLIENT_SECTION = 'client_section';
    public const ADMIN_TEAM_SECTION = 'admin_team_section';

    public const DEVICES_VEHICLES_IMPORT_DATA = 'devices_vehicles_import_data';

    public const LOGIN_WITH_ID = 'login_with_id';
    public const SET_MOBILE_DEVICE = 'set_mobile_device';
    public const SET_MOBILE_DEVICE_TOKEN = 'set_mobile_device_token';

    public const USER_GROUP_NEW = 'configuration.user_group_list.add';
    public const USER_GROUP_EDIT = 'configuration.user_group_list.edit';
    public const USER_GROUP_DELETE = 'configuration.user_group_list.delete';
    public const USER_GROUP_ARCHIVE = 'configuration.user_group_list.archive';
    public const TRACK_LINK_CREATE = 'map.vehicle.share_tracking_link';
    public const TRACK_LINK_CREATE_MOBILE = 'track_link_create';

    public const DIGITAL_FORM_LIST = 'digital_form_list';
    public const DIGITAL_FORM_VIEW = 'digital_form_view';
    public const DIGITAL_FORM_ANSWER_VIEW = 'digital_form_answer_view';
    public const DIGITAL_FORM_ANSWER_CREATE = 'digital_form_answer_create';

    public const VEHICLE_INSPECTION_FORM_LIST = 'fleet.inspection_forms_list';
    public const VEHICLE_INSPECTION_FORM_CREATE = 'fleet.inspection_forms_list.add';
    public const VEHICLE_INSPECTION_FORM_EDIT = 'fleet.inspection_forms_list.edit';
    public const VEHICLE_INSPECTION_FORM_DELETE = 'fleet.inspection_forms_list.delete';

    public const RESELLER_LIST = 'reseller_list';
    public const RESELLER_NEW = 'reseller_new';
    public const RESELLER_EDIT = 'reseller_edit';
    public const RESELLER_DELETE = 'reseller_delete';
    public const RESELLER_SECTION = 'reseller_section';

    public const RESELLER_USER_LIST = 'reseller_user_list';
    public const RESELLER_USER_NEW = 'reseller_user_new';
    public const RESELLER_USER_EDIT = 'reseller_user_edit';
    public const RESELLER_USER_DELETE = 'reseller_user_delete';
    public const RESELLER_TEAM_SECTION = 'reseller_team_section';
    public const RESELLER_NOTES_HISTORY = 'reseller_notes_history';

    public const LOGIN_AS_RESELLER = 'login_as_reseller';
    public const DEVICE_SENSOR_LIST = 'device.sensors_list';
    public const DEVICE_SENSOR_CREATE = 'device.sensors_list.add';
    public const DEVICE_SENSOR_EDIT = 'device.sensors_list.edit';
    public const DEVICE_SENSOR_DELETE = 'device.sensors_list.delete';

    public const SCHEDULED_REPORT_LIST = 'configuration.scheduled_report_list';
    public const SCHEDULED_REPORT_CREATE = 'configuration.scheduled_report_list.add';
    public const SCHEDULED_REPORT_EDIT = 'configuration.scheduled_report_list.edit';
    public const SCHEDULED_REPORT_DELETE = 'configuration.scheduled_report_list.delete';
    public const PLATFORM_SETTING_ADMIN_EDIT = 'platform_setting_admin_edit';
    public const PLATFORM_SETTING_RESELLER_EDIT = 'platform_setting_reseller_edit';

    public const ASSET_LIST = 'asset.assets_list';
    public const ASSET_NEW = 'asset.assets_list.add';
    public const ASSET_EDIT = 'asset.assets_list.edit';
    public const ASSET_DELETE = 'asset.assets_list.delete';
    public const ASSET_INSTALL_UNINSTALL = 'asset.assets_list.install_uninstall';
    public const ASSET_DASHBOARD = 'asset.dashboard';
    public const ASSET_REMINDER_LIST = 'asset.service_reminders_list';
    public const ASSET_REMINDER_NEW = 'asset.service_reminders_list.add';
    public const ASSET_REMINDER_EDIT = 'asset.service_reminders_list.edit';
    public const ASSET_REMINDER_DELETE = 'asset.service_reminders_list.delete';
    public const ASSET_REMINDER_ARCHIVE = 'asset.service_reminders_list.archive';
    public const ASSET_SERVICE_RECORD_LIST = 'asset.service_reminders_list.records_list';
    public const ASSET_SERVICE_RECORD_NEW = 'asset.service_reminders_list.records_list.add';
    public const ASSET_SERVICE_RECORD_EDIT = 'asset.service_reminders_list.records_list.edit';
    public const ASSET_SERVICE_RECORD_DELETE = 'asset.service_reminders_list.records_list.delete';
    public const ASSET_DOCUMENT_LIST = 'asset.documents_list';
    public const ASSET_DOCUMENT_NEW = 'asset.documents_list.add';
    public const ASSET_DOCUMENT_EDIT = 'asset.documents_list.edit';
    public const ASSET_DOCUMENT_DELETE = 'asset.documents_list.delete';
    public const ASSET_DOCUMENT_ARCHIVE = 'asset.documents_list.archive';
    public const ASSET_DOCUMENT_RECORD_LIST = 'asset.documents_list.records_list';
    public const ASSET_DOCUMENT_RECORD_NEW = 'asset.documents_list.records_list.add';
    public const ASSET_SECTION = 'asset';
    public const ASSET_SECTION_SERVICE_REMINDERS = 'asset_section_service_reminders';
    public const ASSET_SECTION_DOCUMENTS = 'asset_section_documents';
    public const ASSET_SECTION_EDIT_SERVICE_REMINDER = 'asset_section_edit_service_reminder';
    public const ASSET_SECTION_EDIT_DOCUMENTS = 'asset_section_edit_documents';
    public const ASSET_SECTION_EDIT_REPAIR_COSTS = 'asset_section_edit_repair_costs';

    public const DRIVER_BLOCK_USER = 'drivers.drivers_list.block_user';
    public const DRIVER_RESET_PASSWORD = 'drivers.drivers_list.reset_password';
    public const DRIVER_DELETE = 'drivers.drivers_list.delete';
    public const DRIVER_ARCHIVE = 'drivers.drivers_list.archive';
    public const DRIVER_DOCUMENT_LIST = 'drivers.documents_list';
    public const DRIVER_DOCUMENT_NEW = 'drivers.documents_list.add';
    public const DRIVER_DOCUMENT_EDIT = 'drivers.documents_list.edit';
    public const DRIVER_DOCUMENT_RECORD_LIST = 'drivers.documents_list.records_list';
    public const DRIVER_DOCUMENT_RECORD_NEW = 'drivers.documents_list.records_list.add';
    public const DRIVER_DOCUMENT_DELETE = 'drivers.documents_list.delete';
    public const DRIVER_DOCUMENT_ARCHIVE = 'drivers.documents_list.archive';

    public const SUPPORT_SUBMIT_TICKET = 'support.submit_ticket';
    public const SUPPORT_CONTACT_US = 'support.contact_us';
    public const CONFIGURATION_INTEGRATIONS_LIST = 'configuration.integrations_list';

    public const CONFIGURATION_USER_GROUP_LIST_VEHICLES = 'configuration.user_group_list.vehicles';
    public const CONFIGURATION_USER_GROUP_LIST_AREAS = 'configuration.user_group_list.areas';
    public const CONFIGURATION_USER_GROUP_LIST_MODULES = 'configuration.user_group_list.modules';

    public const CHAT_LIST = 'messenger';
    public const CHAT_LIST_ALL = 'messenger.show_all';
    public const CHAT_CREATE = 'messenger.create_chat';

    public const BILLING_PLAN_EDIT = 'billing_plan.edit';
    public const BILLING = 'billing';
    public const MOVE_DEVICE = 'move_device';

    public const BILLING_INVOICE_VIEW = 'billing.invoice_view';
    public const BILLING_INVOICE_PAY = 'billing.invoice_pay';
    public const BILLING_PAYMENT_CHANGE = 'billing.payment_change';
    public const BILLING_INVOICE_CLEAN = 'billing.clean';

    public const XERO_API = 'xero_api';
    public const STRIPE_API = 'stripe_api';

    public const BILLING_ADMIN = 'billing_admin';

    public const FUEL_STATION_CREATE = 'fuel_station.create';
    public const FUEL_STATION_EDIT = 'fuel_station.edit';
    public const FUEL_STATION_DELETE = 'fuel_station.delete';
    public const FUEL_STATION_LIST = 'fuel_station.list';
    public const CAMERAS = 'cameras';

    public const ASSET_PERMISSIONS_ARRAY = [
        Permission::MAP_SECTION_ASSETS,
        Permission::ASSET_DOCUMENT_LIST,
        Permission::ASSET_DOCUMENT_RECORD_LIST,
        Permission::ASSET_DOCUMENT_RECORD_NEW,
        Permission::ASSET_DOCUMENT_DELETE,
        Permission::ASSET_DOCUMENT_EDIT,
        Permission::ASSET_DOCUMENT_NEW,
        Permission::ASSET_LIST,
        Permission::ASSET_NEW,
        Permission::ASSET_EDIT,
        Permission::ASSET_DELETE,
        Permission::ASSET_INSTALL_UNINSTALL,
        Permission::ASSET_DASHBOARD,
        Permission::ASSET_REMINDER_LIST,
        Permission::ASSET_DOCUMENT_LIST,
        Permission::ASSET_SECTION,
        Permission::ASSET_SECTION_SERVICE_REMINDERS,
        Permission::ASSET_SERVICE_RECORD_DELETE,
        Permission::ASSET_SERVICE_RECORD_EDIT,
        Permission::ASSET_SERVICE_RECORD_NEW,
        Permission::ASSET_SERVICE_RECORD_LIST,
        Permission::ASSET_REMINDER_DELETE,
        Permission::ASSET_REMINDER_NEW,
        Permission::ASSET_REMINDER_EDIT,
        Permission::ASSET_SECTION_DOCUMENTS,
        Permission::ASSET_SECTION_EDIT_SERVICE_REMINDER,
        Permission::ASSET_SECTION_EDIT_DOCUMENTS,
        Permission::ASSET_SECTION_EDIT_REPAIR_COSTS
    ];

    public const MESSENGER_PERMISSIONS_ARRAY = [
        Permission::CHAT_LIST,
        Permission::CHAT_LIST_ALL,
        Permission::CHAT_CREATE
    ];

    public const BILLING_PERMISSIONS_ARRAY = [
        Permission::BILLING,
        Permission::BILLING_INVOICE_PAY,
        Permission::BILLING_INVOICE_VIEW,
        Permission::BILLING_PAYMENT_CHANGE
    ];

    public const UI_MENU_SECTION_PERMISSIONS_ARRAY = [
        Permission::MAP_SECTION,
        Permission::FLEET_SECTION,
        Permission::FUEL_SECTION,
        Permission::DRIVERS_SECTION,
        Permission::DRIVING_BEHAVIOUR_SECTION,
        Permission::ASSET_SECTION,
        Permission::ALERTS_SECTION,
        Permission::REPORTS_SECTION,
        Permission::CHAT_LIST,
        Permission::DEVICE,
        Permission::CONFIGURATION_SECTION,
    ];

    public const MOBILE_MAPPER = [
        self::MAP_SECTION_GEOFENCES => self::MAP_SECTION_GEOFENCES_MOBILE,
        self::VEHICLE_REMINDER_LIST => self::FLEET_SECTION_SERVICE_REMINDERS,
        self::VEHICLE_DOCUMENT_LIST => self::FLEET_SECTION_DOCUMENTS,
        self::REPAIR_COST_LIST => self::REPAIR_COST_LIST_MOBILE,
        self::REPAIR_COST_NEW => self::REPAIR_COST_NEW_MOBILE,
        self::REPAIR_COST_EDIT => self::REPAIR_COST_EDIT_MOBILE,
        self::DRIVING_BEHAVIOUR_SECTION => self::DRIVING_BEHAVIOUR_SECTION_MOBILE,
        self::DRIVING_BEHAVIOUR_DASHBOARD => self::DRIVING_BEHAVIOUR_DASHBOARD_MOBILE,
        self::DRIVING_BEHAVIOUR_VEHICLES => self::DRIVING_BEHAVIOUR_VEHICLES_MOBILE,
        self::DRIVING_BEHAVIOUR_DRIVERS => self::DRIVING_BEHAVIOUR_DRIVERS_MOBILE,
        self::VEHICLE_INSPECTION_FORM_LIST => self::DIGITAL_FORM_VIEW,
        self::VEHICLE_GROUP_LIST => self::VEHICLE_GROUP_LIST_MOBILE,
        self::DEPOT_LIST => self::DEPOT_LIST_MOBILE,
        self::TRACK_LINK_CREATE => self::TRACK_LINK_CREATE_MOBILE,
        self::FLEET_SECTION_ADD_VEHICLE => self::VEHICLE_NEW,
        self::DEVICE_LIST => self::DEVICE_BY_ID
    ];

    /**
     * Permission constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->displayName = $fields['displayName'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'displayName' => $this->displayName
        ];
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'display_name', type: 'string', length: 255)]
    private $displayName;

    /**
     * Many Permissions Groups have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'permissions', fetch: 'EXTRA_LAZY')]
    private $userGroups;

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Permission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Permission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     *
     * @return Permission
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}