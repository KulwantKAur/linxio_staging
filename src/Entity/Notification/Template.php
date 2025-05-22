<?php

namespace App\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Template
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Template with this name already exists."
 * )
 */
#[ORM\Table(name: 'notification_template')]
#[ORM\UniqueConstraint(columns: ['name', 'transport_id', 'type'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\TemplateRepository')]
class Template
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_CUSTOM = 'custom';

    public const USER_CREATED_USER_EMAIL = "user_created_user_email";
    public const USER_CREATED_USER_SMS = "user_created_user_sms";
    public const USER_CREATED_USER_MOBILE = "user_created_user_mobile";
    public const USER_CREATED_USER_WEB = "user_created_user_web";

    public const USER_CREATED_SYSTEM_EMAIL = "user_created_system_email";

    public const USER_PSW_RESET_USER_EMAIL = "user_pwd_reset_user_email";
    public const USER_PSW_RESET_USER_SMS = "user_pwd_reset_user_sms";
    public const USER_PSW_RESET_USER_MOBILE = "user_pwd_reset_user_mobile";
    public const USER_PSW_RESET_USER_WEB = "user_pwd_reset_user_web";

    public const USER_BLOCKED_USER_EMAIL = "user_blocked_user_email";
    public const USER_BLOCKED_USER_SMS = "user_blocked_user_sms";
    public const USER_BLOCKED_USER_MOBILE = "user_blocked_user_mobile";
    public const USER_BLOCKED_USER_WEB = "user_blocked_user_web";

    public const USER_DELETED_USER_EMAIL = "user_deleted_user_email";
    public const USER_DELETED_USER_SMS = "user_deleted_user_sms";
    public const USER_DELETED_USER_MOBILE = "user_deleted_user_mobile";
    public const USER_DELETED_USER_WEB = "user_deleted_user_web";

    public const USER_CHANGED_NAME_USER_MOBILE = "user_changed_name_user_mobile";
    public const USER_CHANGED_NAME_USER_WEB = "user_changed_name_user_web";
    public const USER_CHANGED_NAME_USER_SMS = "user_changed_name_user_sms";
    public const USER_CHANGED_NAME_USER_EMAIL = "user_changed_name_user_email";

    public const USER_CHANGED_SURNAME_USER_MOBILE = "user_changed_surname_user_mobile";
    public const USER_CHANGED_SURNAME_USER_WEB = "user_changed_surname_user_web";
    public const USER_CHANGED_SURNAME_USER_SMS = "user_changed_surname_user_sms";
    public const USER_CHANGED_SURNAME_USER_EMAIL = "user_changed_surname_user_email";

    public const CLIENT_CREATED_USER_EMAIL = "client_created_user_email";
    public const CLIENT_CREATED_USER_SMS = "client_created_user_sms";
    public const CLIENT_CREATED_USER_MOBILE = "client_created_user_mobile";
    public const CLIENT_CREATED_USER_WEB = "client_created_user_web";

    public const CLIENT_BLOCKED_USER_EMAIL = "client_blocked_user_email";
    public const CLIENT_BLOCKED_USER_SMS = "client_blocked_user_sms";
    public const CLIENT_BLOCKED_USER_MOBILE = "client_blocked_user_mobile";
    public const CLIENT_BLOCKED_USER_WEB = "client_blocked_user_web";

    public const CLIENT_DEMO_EXPIRED_USER_EMAIL = "client_demo_expired_user_email";
    public const CLIENT_DEMO_EXPIRED_USER_SMS = "client_demo_expired_user_sms";
    public const CLIENT_DEMO_EXPIRED_USER_MOBILE = "client_demo_expired_user_mobile";
    public const CLIENT_DEMO_EXPIRED_USER_WEB = "client_demo_expired_user_web";

    public const LOGIN_AS_CLIENT_USER_WEB = "login_as_client_user_web";
    public const LOGIN_AS_CLIENT_USER_MOBILE = "login_as_client_user_mobile";
    public const LOGIN_AS_CLIENT_USER_SMS = "login_as_client_user_sms";
    public const LOGIN_AS_CLIENT_USER_EMAIL = "login_as_client_user_email";

    public const LOGIN_AS_USER_USER_WEB = "login_as_user_user_web";
    public const LOGIN_AS_USER_USER_MOBILE = "login_as_user_user_mobile";
    public const LOGIN_AS_USER_USER_SMS = "login_as_user_user_sms";
    public const LOGIN_AS_USER_USER_EMAIL = "login_as_user_user_email";

    public const VEHICLE_CREATED_USER_EMAIL = "vehicle_created_user_email";
    public const VEHICLE_CREATED_USER_SMS = "vehicle_created_user_sms";
    public const VEHICLE_CREATED_USER_MOBILE = "vehicle_created_user_mobile";
    public const VEHICLE_CREATED_USER_WEB = "vehicle_created_user_web";

    public const VEHICLE_DELETED_USER_EMAIL = "vehicle_deleted_user_email";
    public const VEHICLE_DELETED_USER_SMS = "vehicle_deleted_user_sms";
    public const VEHICLE_DELETED_USER_MOBILE = "vehicle_deleted_user_mobile";
    public const VEHICLE_DELETED_USER_WEB = "vehicle_deleted_user_web";

    public const VEHICLE_CHANGED_REGNO_USER_EMAIL = "vehicle_changed_regno_email";
    public const VEHICLE_CHANGED_REGNO_USER_SMS = "vehicle_changed_regno_sms";
    public const VEHICLE_CHANGED_REGNO_USER_MOBILE = "vehicle_changed_regno_mobile";
    public const VEHICLE_CHANGED_REGNO_USER_WEB = "vehicle_changed_regno_web";

    public const VEHICLE_CHANGED_MODEL_USER_EMAIL = "vehicle_changed_model_email";
    public const VEHICLE_CHANGED_MODEL_USER_SMS = "vehicle_changed_model_sms";
    public const VEHICLE_CHANGED_MODEL_USER_MOBILE = "vehicle_changed_model_mobile";
    public const VEHICLE_CHANGED_MODEL_USER_WEB = "vehicle_changed_model_web";

    public const VEHICLE_GEOFENCE_ENTER_USER_WEB = "vehicle_geofence_enter_user_web";
    public const VEHICLE_GEOFENCE_ENTER_USER_MOBILE = "vehicle_geofence_enter_user_mobile";
    public const VEHICLE_GEOFENCE_ENTER_USER_SMS = "vehicle_geofence_enter_user_sms";
    public const VEHICLE_GEOFENCE_ENTER_USER_EMAIL = "vehicle_geofence_enter_user_email";

    public const VEHICLE_GEOFENCE_LEAVE_USER_WEB = "vehicle_geofence_leave_user_web";
    public const VEHICLE_GEOFENCE_LEAVE_USER_MOBILE = "vehicle_geofence_leave_user_mobile";
    public const VEHICLE_GEOFENCE_LEAVE_USER_SMS = "vehicle_geofence_leave_user_sms";
    public const VEHICLE_GEOFENCE_LEAVE_USER_EMAIL = "vehicle_geofence_leave_user_email";

    public const VEHICLE_UNAVAILABLE_USER_EMAIL = "vehicle_unavailable_user_email";
    public const VEHICLE_UNAVAILABLE_USER_WEB = "vehicle_unavailable_user_web";
    public const VEHICLE_UNAVAILABLE_USER_SMS = "vehicle_unavailable_user_sms";
    public const VEHICLE_UNAVAILABLE_USER_MOBILE = "vehicle_unavailable_user_mobile";

    public const VEHICLE_OFFLINE_USER_EMAIL = "vehicle_offline_user_email";
    public const VEHICLE_OFFLINE_USER_WEB = "vehicle_offline_user_web";
    public const VEHICLE_OFFLINE_USER_SMS = "vehicle_offline_user_sms";
    public const VEHICLE_OFFLINE_USER_MOBILE = "vehicle_offline_user_mobile";

    public const VEHICLE_REASSIGNED_USER_WEB = "vehicle_reassigned_user_web";
    public const VEHICLE_REASSIGNED_USER_MOBILE = "vehicle_reassigned_user_mobile";
    public const VEHICLE_REASSIGNED_USER_SMS = "vehicle_reassigned_user_sms";
    public const VEHICLE_REASSIGNED_USER_EMAIL = "vehicle_reassigned_user_email";

    public const VEHICLE_ONLINE_USER_WEB = "vehicle_online_user_web";
    public const VEHICLE_ONLINE_USER_SMS = "vehicle_online_user_sms";
    public const VEHICLE_ONLINE_USER_MOBILE = "vehicle_online_user_mobile";
    public const VEHICLE_ONLINE_USER_EMAIL = "vehicle_online_user_email";

    public const VEHICLE_OVER_SPEEDING_USER_WEB = "vehicle_over_speeding_user_web";
    public const VEHICLE_OVER_SPEEDING_USER_SMS = "vehicle_over_speeding_user_sms";
    public const VEHICLE_OVER_SPEEDING_USER_MOBILE = "vehicle_over_speeding_user_mobile";
    public const VEHICLE_OVER_SPEEDING_USER_EMAIL = "vehicle_over_speeding_user_email";

    public const VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_WEB = "vehicle_over_speeding_inside_geofence_user_web";
    public const VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_SMS = "vehicle_over_speeding_inside_geofence_user_sms";
    public const VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_MOBILE = "vehicle_over_speeding_inside_geofence_user_mobile";
    public const VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_EMAIL = "vehicle_over_speeding_inside_geofence_user_email";

    public const VEHICLE_DRIVING_WITHOUT_DRIVER_USER_WEB = "vehicle_driving_without_driver_user_web";
    public const VEHICLE_DRIVING_WITHOUT_DRIVER_USER_SMS = "vehicle_driving_without_driver_user_sms";
    public const VEHICLE_DRIVING_WITHOUT_DRIVER_USER_MOBILE = "vehicle_driving_without_driver_user_mobile";
    public const VEHICLE_DRIVING_WITHOUT_DRIVER_USER_EMAIL = "vehicle_driving_without_driver_user_email";

    public const VEHICLE_TOWING_EVENT_USER_WEB = "vehicle_towing_event_user_web";
    public const VEHICLE_TOWING_EVENT_USER_SMS = "vehicle_towing_event_user_sms";
    public const VEHICLE_TOWING_EVENT_USER_MOBILE = "vehicle_towing_event_user_mobile";
    public const VEHICLE_TOWING_EVENT_USER_EMAIL = "vehicle_towing_event_user_email";

    public const VEHICLE_LONG_STANDING_USER_WEB = "vehicle_long_standing_user_web";
    public const VEHICLE_LONG_STANDING_USER_SMS = "vehicle_long_standing_user_sms";
    public const VEHICLE_LONG_STANDING_USER_MOBILE = "vehicle_long_standing_user_mobile";
    public const VEHICLE_LONG_STANDING_USER_EMAIL = "vehicle_long_standing_user_email";

    public const VEHICLE_LONG_DRIVING_USER_WEB = "vehicle_long_driving_user_web";
    public const VEHICLE_LONG_DRIVING_USER_SMS = "vehicle_long_driving_user_sms";
    public const VEHICLE_LONG_DRIVING_USER_MOBILE = "vehicle_long_driving_user_mobile";
    public const VEHICLE_LONG_DRIVING_USER_EMAIL = "vehicle_long_driving_user_email";

    public const VEHICLE_MOVING_USER_WEB = "vehicle_moving_user_web";
    public const VEHICLE_MOVING_USER_SMS = "vehicle_moving_user_sms";
    public const VEHICLE_MOVING_USER_MOBILE = "vehicle_moving_user_mobile";
    public const VEHICLE_MOVING_USER_EMAIL = "vehicle_moving_user_email";

    public const VEHICLE_EXCESSING_IDLING_USER_WEB = "vehicle_excessing_idling_user_web";
    public const VEHICLE_EXCESSING_IDLING_USER_SMS = "vehicle_excessing_idling_user_sms";
    public const VEHICLE_EXCESSING_IDLING_USER_MOBILE = "vehicle_excessing_idling_user_mobile";
    public const VEHICLE_EXCESSING_IDLING_USER_EMAIL = "vehicle_excessing_idling_user_email";

    public const DOCUMENT_EXPIRED_USER_WEB = "document_expired_user_web";
    public const DOCUMENT_EXPIRED_USER_SMS = "document_expired_user_sms";
    public const DOCUMENT_EXPIRED_USER_MOBILE = "document_expired_user_mobile";
    public const DOCUMENT_EXPIRED_USER_EMAIL = "document_expired_user_email";

    public const DRIVER_DOCUMENT_EXPIRED_USER_WEB = "driver_document_expired_user_web";
    public const DRIVER_DOCUMENT_EXPIRED_USER_SMS = "driver_document_expired_user_sms";
    public const DRIVER_DOCUMENT_EXPIRED_USER_MOBILE = "driver_document_expired_user_mobile";
    public const DRIVER_DOCUMENT_EXPIRED_USER_EMAIL = "driver_document_expired_user_email";

    public const ASSET_DOCUMENT_EXPIRED_USER_WEB = "asset_document_expired_user_web";
    public const ASSET_DOCUMENT_EXPIRED_USER_SMS = "asset_document_expired_user_sms";
    public const ASSET_DOCUMENT_EXPIRED_USER_MOBILE = "asset_document_expired_user_mobile";
    public const ASSET_DOCUMENT_EXPIRED_USER_EMAIL = "asset_document_expired_user_email";

    public const DOCUMENT_EXPIRE_SOON_USER_WEB = "document_expire_soon_user_web";
    public const DOCUMENT_EXPIRE_SOON_USER_SMS = "document_expire_soon_user_sms";
    public const DOCUMENT_EXPIRE_SOON_USER_MOBILE = "document_expire_soon_user_mobile";
    public const DOCUMENT_EXPIRE_SOON_USER_EMAIL = "document_expire_soon_user_email";

    public const ASSET_DOCUMENT_EXPIRE_SOON_USER_WEB = "asset_document_expire_soon_user_web";
    public const ASSET_DOCUMENT_EXPIRE_SOON_USER_SMS = "asset_document_expire_soon_user_sms";
    public const ASSET_DOCUMENT_EXPIRE_SOON_USER_MOBILE = "asset_document_expire_soon_user_mobile";
    public const ASSET_DOCUMENT_EXPIRE_SOON_USER_EMAIL = "asset_document_expire_soon_user_email";

    public const DRIVER_DOCUMENT_EXPIRE_SOON_USER_WEB = "driver_document_expire_soon_user_web";
    public const DRIVER_DOCUMENT_EXPIRE_SOON_USER_SMS = "driver_document_expire_soon_user_sms";
    public const DRIVER_DOCUMENT_EXPIRE_SOON_USER_MOBILE = "driver_document_expire_soon_user_mobile";
    public const DRIVER_DOCUMENT_EXPIRE_SOON_USER_EMAIL = "driver_document_expire_soon_user_email";

    public const DOCUMENT_DELETED_USER_WEB = "document_deleted_user_web";
    public const DOCUMENT_DELETED_USER_SMS = "document_deleted_user_sms";
    public const DOCUMENT_DELETED_USER_MOBILE = "document_deleted_user_mobile";
    public const DOCUMENT_DELETED_USER_EMAIL = "document_deleted_user_email";

    public const DRIVER_DOCUMENT_DELETED_USER_WEB = "driver_document_deleted_user_web";
    public const DRIVER_DOCUMENT_DELETED_USER_SMS = "driver_document_deleted_user_sms";
    public const DRIVER_DOCUMENT_DELETED_USER_MOBILE = "driver_document_deleted_user_mobile";
    public const DRIVER_DOCUMENT_DELETED_USER_EMAIL = "driver_document_deleted_user_email";

    public const ASSET_DOCUMENT_DELETED_USER_WEB = "asset_document_deleted_user_web";
    public const ASSET_DOCUMENT_DELETED_USER_SMS = "asset_document_deleted_user_sms";
    public const ASSET_DOCUMENT_DELETED_USER_MOBILE = "asset_document_deleted_user_mobile";
    public const ASSET_DOCUMENT_DELETED_USER_EMAIL = "asset_document_deleted_user_email";

    public const DOCUMENT_RECORD_ADDED_USER_WEB = "document_record_added_user_web";
    public const DOCUMENT_RECORD_ADDED_USER_SMS = "document_record_added_user_sms";
    public const DOCUMENT_RECORD_ADDED_USER_MOBILE = "document_record_added_user_mobile";
    public const DOCUMENT_RECORD_ADDED_USER_EMAIL = "document_record_added_user_email";

    public const DRIVER_DOCUMENT_RECORD_ADDED_USER_WEB = "driver_document_record_added_user_web";
    public const DRIVER_DOCUMENT_RECORD_ADDED_USER_SMS = "driver_document_record_added_user_sms";
    public const DRIVER_DOCUMENT_RECORD_ADDED_USER_MOBILE = "driver_document_record_added_user_mobile";
    public const DRIVER_DOCUMENT_RECORD_ADDED_USER_EMAIL = "driver_document_record_added_user_email";

    public const ASSET_DOCUMENT_RECORD_ADDED_USER_WEB = "asset_document_record_added_user_web";
    public const ASSET_DOCUMENT_RECORD_ADDED_USER_SMS = "asset_document_record_added_user_sms";
    public const ASSET_DOCUMENT_RECORD_ADDED_USER_MOBILE = "asset_document_record_added_user_mobile";
    public const ASSET_DOCUMENT_RECORD_ADDED_USER_EMAIL = "asset_document_record_added_user_email";

    public const DEVICE_IN_STOCK_USER_WEB = "device_in_stock_user_web";
    public const DEVICE_IN_STOCK_USER_SMS = "device_in_stock_user_sms";
    public const DEVICE_IN_STOCK_USER_MOBILE = "device_in_stock_user_mobile";
    public const DEVICE_IN_STOCK_USER_EMAIL = "device_in_stock_user_email";

    public const DEVICE_OFFLINE_USER_WEB = "device_offline_user_web";
    public const DEVICE_OFFLINE_USER_SMS = "device_offline_user_sms";
    public const DEVICE_OFFLINE_USER_MOBILE = "device_offline_user_mobile";
    public const DEVICE_OFFLINE_USER_EMAIL = "device_offline_user_email";

    public const DEVICE_UNAVAILABLE_USER_WEB = "device_unavailable_user_web";
    public const DEVICE_UNAVAILABLE_USER_SMS = "device_unavailable_user_sms";
    public const DEVICE_UNAVAILABLE_USER_MOBILE = "device_unavailable_user_mobile";
    public const DEVICE_UNAVAILABLE_USER_EMAIL = "device_unavailable_user_email";

    public const DEVICE_DELETED_USER_WEB = "device_deleted_user_web";
    public const DEVICE_DELETED_USER_SMS = "device_deleted_user_sms";
    public const DEVICE_DELETED_USER_MOBILE = "device_deleted_user_mobile";
    public const DEVICE_DELETED_USER_EMAIL = "device_deleted_user_email";

    public const DEVICE_REPLACED_USER_WEB = "device_replaced_user_web";
    public const DEVICE_REPLACED_USER_SMS = "device_replaced_user_sms";
    public const DEVICE_REPLACED_USER_MOBILE = "device_replaced_user_mobile";
    public const DEVICE_REPLACED_USER_EMAIL = "device_replaced_user_email";

    public const SERVICE_REMINDER_SOON_USER_WEB = "service_reminder_soon_user_web";
    public const SERVICE_REMINDER_SOON_USER_SMS = "service_reminder_soon_user_sms";
    public const SERVICE_REMINDER_SOON_USER_MOBILE = "service_reminder_soon_user_mobile";
    public const SERVICE_REMINDER_SOON_USER_EMAIL = "service_reminder_soon_user_email";

    public const SERVICE_REMINDER_EXPIRED_USER_WEB = "service_reminder_expired_user_web";
    public const SERVICE_REMINDER_EXPIRED_USER_SMS = "service_reminder_expired_user_sms";
    public const SERVICE_REMINDER_EXPIRED_USER_MOBILE = "service_reminder_expired_user_mobile";
    public const SERVICE_REMINDER_EXPIRED_USER_EMAIL = "service_reminder_expired_user_email";

    public const SERVICE_REMINDER_DONE_USER_WEB = "service_reminder_done_user_web";
    public const SERVICE_REMINDER_DONE_USER_SMS = "service_reminder_done_user_sms";
    public const SERVICE_REMINDER_DONE_USER_MOBILE = "service_reminder_done_user_mobile";
    public const SERVICE_REMINDER_DONE_USER_EMAIL = "service_reminder_done_user_email";

    public const SERVICE_REMINDER_DELETED_USER_WEB = "service_reminder_deleted_user_web";
    public const SERVICE_REMINDER_DELETED_USER_SMS = "service_reminder_deleted_user_sms";
    public const SERVICE_REMINDER_DELETED_USER_MOBILE = "service_reminder_deleted_user_mobile";
    public const SERVICE_REMINDER_DELETED_USER_EMAIL = "service_reminder_deleted_user_email";

    public const SERVICE_RECORD_ADDED_USER_WEB = "service_record_added_user_web";
    public const SERVICE_RECORD_ADDED_USER_SMS = "service_record_added_user_sms";
    public const SERVICE_RECORD_ADDED_USER_MOBILE = "service_record_added_user_mobile";
    public const SERVICE_RECORD_ADDED_USER_EMAIL = "service_record_added_user_email";

    public const SERVICE_REPAIR_ADDED_USER_WEB = "service_repair_added_user_web";
    public const SERVICE_REPAIR_ADDED_USER_SMS = "service_repair_added_user_sms";
    public const SERVICE_REPAIR_ADDED_USER_MOBILE = "service_repair_added_user_mobile";
    public const SERVICE_REPAIR_ADDED_USER_EMAIL = "service_repair_added_user_email";

    public const TRACKER_VOLTAGE_USER_WEB = "tracker_voltage_user_web";
    public const TRACKER_VOLTAGE_USER_SMS = "tracker_voltage_user_sms";
    public const TRACKER_VOLTAGE_USER_MOBILE = "tracker_voltage_user_mobile";
    public const TRACKER_VOLTAGE_USER_EMAIL = "tracker_voltage_user_email";

    public const DEVICE_UNKNOWN_DETECTED_USER_WEB = "tracker_unknown_detected_user_web";
    public const DEVICE_UNKNOWN_DETECTED_USER_SMS = "tracker_unknown_detected_user_sms";
    public const DEVICE_UNKNOWN_DETECTED_USER_MOBILE = "tracker_unknown_detected_user_mobile";
    public const DEVICE_UNKNOWN_DETECTED_USER_EMAIL = "tracker_unknown_detected_user_email";

    public const PANIC_BUTTON_USER_EMAIL = "panic_button_user_email";
    public const PANIC_BUTTON_USER_SMS = "panic_button_user_sms";
    public const PANIC_BUTTON_USER_MOBILE = "panic_button_user_mobile";
    public const PANIC_BUTTON_USER_WEB = "panic_button_user_web";

    public const TRACKER_JAMMER_STARTED_USER_WEB = "tracker_jammer_started_user_web";
    public const TRACKER_JAMMER_STARTED_USER_SMS = "tracker_jammer_started_user_sms";
    public const TRACKER_JAMMER_STARTED_USER_MOBILE = "tracker_jammer_started_user_mobile";
    public const TRACKER_JAMMER_STARTED_USER_EMAIL = "tracker_jammer_started_user_email";

    public const TRACKER_ACCIDENT_HAPPENED_USER_WEB = "tracker_accident_happened_user_web";
    public const TRACKER_ACCIDENT_HAPPENED_USER_SMS = "tracker_accident_happened_user_sms";
    public const TRACKER_ACCIDENT_HAPPENED_USER_MOBILE = "tracker_accident_happened_user_mobile";
    public const TRACKER_ACCIDENT_HAPPENED_USER_EMAIL = "tracker_accident_happened_user_email";

    public const ODOMETER_CORRECTED_USER_EMAIL = "odometer_corrected_user_email";
    public const ODOMETER_CORRECTED_USER_SMS = "odometer_corrected_user_sms";
    public const ODOMETER_CORRECTED_USER_MOBILE = "odometer_corrected_user_mobile";
    public const ODOMETER_CORRECTED_USER_WEB = "odometer_corrected_user_web";

    public const DIGITAL_FORM_WITH_FAIL_USER_EMAIL = "digital_form_with_fail_user_email";
    public const DIGITAL_FORM_WITH_FAIL_USER_SMS = "digital_form_with_fail_user_sms";
    public const DIGITAL_FORM_WITH_FAIL_USER_MOBILE = "digital_form_with_fail_user_mobile";
    public const DIGITAL_FORM_WITH_FAIL_USER_WEB = "digital_form_with_fail_user_web";

    public const DIGITAL_FORM_IS_NOT_COMPLETED_USER_EMAIL = "digital_form_is_not_completed_user_email";
    public const DIGITAL_FORM_IS_NOT_COMPLETED_USER_SMS = "digital_form_is_not_completed_user_sms";
    public const DIGITAL_FORM_IS_NOT_COMPLETED_USER_MOBILE = "digital_form_is_not_completed_user_mobile";
    public const DIGITAL_FORM_IS_NOT_COMPLETED_USER_WEB = "digital_form_is_not_completed_user_web";
    public const DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_WEB = "digital_form_is_not_completed_system_web";
    public const DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_MOBILE = "digital_form_is_not_completed_system_mobile";

    public const SENSOR_TEMPERATURE_USER_EMAIL = "sensor_temperature_user_email";
    public const SENSOR_TEMPERATURE_USER_SMS = "sensor_temperature_user_sms";
    public const SENSOR_TEMPERATURE_USER_MOBILE = "sensor_temperature_user_mobile";
    public const SENSOR_TEMPERATURE_USER_WEB = "sensor_temperature_user_web";

    public const SENSOR_HUMIDITY_USER_EMAIL = "sensor_humidity_user_email";
    public const SENSOR_HUMIDITY_USER_SMS = "sensor_humidity_user_sms";
    public const SENSOR_HUMIDITY_USER_MOBILE = "sensor_humidity_user_mobile";
    public const SENSOR_HUMIDITY_USER_WEB = "sensor_humidity_user_web";

    public const SENSOR_LIGHT_USER_EMAIL = "sensor_light_user_email";
    public const SENSOR_LIGHT_USER_SMS = "sensor_light_user_sms";
    public const SENSOR_LIGHT_USER_MOBILE = "sensor_light_user_mobile";
    public const SENSOR_LIGHT_USER_WEB = "sensor_light_user_web";

    public const SENSOR_BATTERY_LEVEL_USER_EMAIL = "sensor_battery_level_user_email";
    public const SENSOR_BATTERY_LEVEL_USER_SMS = "sensor_battery_level_user_sms";
    public const SENSOR_BATTERY_LEVEL_USER_MOBILE = "sensor_battery_level_user_mobile";
    public const SENSOR_BATTERY_LEVEL_USER_WEB = "sensor_battery_level_user_web";

    public const SENSOR_STATUS_USER_EMAIL = "sensor_status_user_email";
    public const SENSOR_STATUS_USER_SMS = "sensor_status_user_sms";
    public const SENSOR_STATUS_USER_MOBILE = "sensor_status_user_mobile";
    public const SENSOR_STATUS_USER_WEB = "sensor_status_user_web";

    public const SENSOR_IO_STATUS_USER_EMAIL = "sensor_io_status_user_email";
    public const SENSOR_IO_STATUS_USER_SMS = "sensor_io_status_user_sms";
    public const SENSOR_IO_STATUS_USER_MOBILE = "sensor_io_status_user_mobile";
    public const SENSOR_IO_STATUS_USER_WEB = "sensor_io_status_user_web";

    public const ASSET_CREATED_USER_EMAIL = "asset_created_user_email";
    public const ASSET_CREATED_USER_SMS = "asset_created_user_sms";
    public const ASSET_CREATED_USER_MOBILE = "asset_created_user_mobile";
    public const ASSET_CREATED_USER_WEB = "asset_created_user_web";

    public const ASSET_DELETED_USER_EMAIL = "asset_deleted_user_email";
    public const ASSET_DELETED_USER_SMS = "asset_deleted_user_sms";
    public const ASSET_DELETED_USER_MOBILE = "asset_deleted_user_mobile";
    public const ASSET_DELETED_USER_WEB = "asset_deleted_user_web";

    public const ASSET_MISSED_USER_EMAIL = "asset_missed_user_email";
    public const ASSET_MISSED_USER_SMS = "asset_missed_user_sms";
    public const ASSET_MISSED_USER_MOBILE = "asset_missed_user_mobile";
    public const ASSET_MISSED_USER_WEB = "asset_missed_user_web";

    public const STRIPE_INTEGRATION_ERROR_USER_EMAIL = 'stripe_integration_error_user_email';
    public const STRIPE_INTEGRATION_ERROR_USER_SMS = 'stripe_integration_error_user_sms';
    public const STRIPE_INTEGRATION_ERROR_USER_MOBILE = 'stripe_integration_error_user_mobile';
    public const STRIPE_INTEGRATION_ERROR_USER_WEB = 'stripe_integration_error_user_web';

    public const STRIPE_PAYMENT_FAILED_USER_EMAIL = 'stripe_payment_failed_user_email';
    public const STRIPE_PAYMENT_FAILED_USER_SMS = 'stripe_payment_failed_user_sms';
    public const STRIPE_PAYMENT_FAILED_USER_MOBILE = 'stripe_payment_failed_user_mobile';
    public const STRIPE_PAYMENT_FAILED_USER_WEB = 'stripe_payment_failed_user_web';

    public const STRIPE_PAYMENT_SUCCESSFUL_USER_EMAIL = 'stripe_payment_successful_user_email';
    public const STRIPE_PAYMENT_SUCCESSFUL_USER_SMS = 'stripe_payment_successful_user_sms';
    public const STRIPE_PAYMENT_SUCCESSFUL_USER_MOBILE = 'stripe_payment_successful_user_mobile';
    public const STRIPE_PAYMENT_SUCCESSFUL_USER_WEB = 'stripe_payment_successful_user_web';

    public const XERO_INTEGRATION_ERROR_USER_EMAIL = 'xero_integration_error_user_email';
    public const XERO_INTEGRATION_ERROR_USER_SMS = 'xero_integration_error_user_sms';
    public const XERO_INTEGRATION_ERROR_USER_MOBILE = 'xero_integration_error_user_mobile';
    public const XERO_INTEGRATION_ERROR_USER_WEB = 'xero_integration_error_user_web';

    public const XERO_INVOICE_CREATION_ERROR_USER_EMAIL = 'xero_invoice_creation_error_user_email';
    public const XERO_INVOICE_CREATION_ERROR_USER_SMS = 'xero_invoice_creation_error_user_sms';
    public const XERO_INVOICE_CREATION_ERROR_USER_MOBILE = 'xero_invoice_creation_error_user_mobile';
    public const XERO_INVOICE_CREATION_ERROR_USER_WEB = 'xero_invoice_creation_error_user_web';

    public const XERO_INVOICE_CREATED_USER_EMAIL = 'xero_invoice_created_user_email';
    public const XERO_INVOICE_CREATED_USER_SMS = 'xero_invoice_created_user_sms';
    public const XERO_INVOICE_CREATED_USER_MOBILE = 'xero_invoice_created_user_mobile';
    public const XERO_INVOICE_CREATED_USER_WEB = 'xero_invoice_created_user_web';

    public const XERO_PAYMENT_CREATION_ERROR_USER_EMAIL = 'xero_payment_creation_error_user_email';
    public const XERO_PAYMENT_CREATION_ERROR_USER_SMS = 'xero_payment_creation_error_user_sms';
    public const XERO_PAYMENT_CREATION_ERROR_USER_MOBILE = 'xero_payment_creation_error_user_mobile';
    public const XERO_PAYMENT_CREATION_ERROR_USER_WEB = 'xero_payment_creation_error_user_web';

    public const XERO_PAYMENT_CREATED_USER_EMAIL = 'xero_payment_created_user_email';
    public const XERO_PAYMENT_CREATED_USER_SMS = 'xero_payment_created_user_sms';
    public const XERO_PAYMENT_CREATED_USER_MOBILE = 'xero_payment_created_user_mobile';
    public const XERO_PAYMENT_CREATED_USER_WEB = 'xero_payment_created_user_web';

    public const INVOICE_CREATED_USER_EMAIL = 'invoice_created_user_email';
    public const INVOICE_CREATED_USER_SMS = 'invoice_created_user_sms';
    public const INVOICE_CREATED_USER_MOBILE = 'invoice_created_user_mobile';
    public const INVOICE_CREATED_USER_WEB = 'invoice_created_user_web';

    public const PAYMENT_FAILED_USER_EMAIL = 'payment_failed_user_email';
    public const PAYMENT_FAILED_USER_SMS = 'payment_failed_user_sms';
    public const PAYMENT_FAILED_USER_MOBILE = 'payment_failed_user_mobile';
    public const PAYMENT_FAILED_USER_WEB = 'payment_failed_user_web';

    public const PAYMENT_SUCCESSFUL_USER_EMAIL = 'payment_successful_user_email';
    public const PAYMENT_SUCCESSFUL_USER_SMS = 'payment_successful_user_sms';
    public const PAYMENT_SUCCESSFUL_USER_MOBILE = 'payment_successful_user_mobile';
    public const PAYMENT_SUCCESSFUL_USER_WEB = 'payment_successful_user_web';

    public const INVOICE_OVERDUE_USER_EMAIL = 'invoice_overdue_user_email';
    public const INVOICE_OVERDUE_USER_SMS = 'invoice_overdue_user_sms';
    public const INVOICE_OVERDUE_USER_MOBILE = 'invoice_overdue_user_mobile';
    public const INVOICE_OVERDUE_USER_WEB = 'invoice_overdue_user_web';

    public const INVOICE_OVERDUE_BLOCKED_USER_EMAIL = 'invoice_overdue_blocked_user_email';
    public const INVOICE_OVERDUE_BLOCKED_USER_SMS = 'invoice_overdue_blocked_user_sms';
    public const INVOICE_OVERDUE_BLOCKED_USER_MOBILE = 'invoice_overdue_blocked_user_mobile';
    public const INVOICE_OVERDUE_BLOCKED_USER_WEB = 'invoice_overdue_blocked_user_web';

    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_EMAIL = 'invoice_overdue_partially_blocked_user_email';
    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_SMS = 'invoice_overdue_partially_blocked_user_sms';
    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_MOBILE = 'invoice_overdue_partially_blocked_user_mobile';
    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_WEB = 'invoice_overdue_partially_blocked_user_web';

    public const DEVICE_CONTRACT_EXPIRED_EMAIL = 'device_contract_expired_email';
    public const DEVICE_CONTRACT_EXPIRED_SMS = 'device_contract_expired_sms';
    public const DEVICE_CONTRACT_EXPIRED_MOBILE = 'device_contract_expired_mobile';
    public const DEVICE_CONTRACT_EXPIRED_WEB = 'device_contract_expired_web';

    public const INTEGRATION_ENABLED_USER_EMAIL = 'integration_enabled_user_email';
    public const INTEGRATION_ENABLED_USER_SMS = 'integration_enabled_user_sms';
    public const INTEGRATION_ENABLED_USER_MOBILE = 'integration_enabled_user_mobile';
    public const INTEGRATION_ENABLED_USER_WEB = 'integration_enabled_user_web';

    public const ACCESS_LEVEL_CHANGED_EMAIL = 'access_level_changed_email';
    public const ACCESS_LEVEL_CHANGED_SMS = 'access_level_changed_sms';
    public const ACCESS_LEVEL_CHANGED_MOBILE = 'access_level_changed_mobile';
    public const ACCESS_LEVEL_CHANGED_WEB = 'access_level_changed_web';

    public const TRANSLATE_DOMAIN = 'notification';
    public const TEMPLATE_BODY = [
        Transport::TRANSPORT_EMAIL => [
            'subject',
            'body'
        ],
        Transport::TRANSPORT_SMS => [
            'body'
        ],
        Transport::TRANSPORT_WEB_APP => [
            'subject',
            'body'
        ],
        Transport::TRANSPORT_MOBILE_APP => [
            'subject',
            'body'
        ],
    ];
    public const TRACKER_BATTERY_PERCENTAGE_USER_WEB = "tracker_battery_percentage_user_web";
    public const TRACKER_BATTERY_PERCENTAGE_USER_SMS = "tracker_battery_percentage_user_sms";
    public const TRACKER_BATTERY_PERCENTAGE_USER_MOBILE = "tracker_battery_percentage_user_mobile";
    public const TRACKER_BATTERY_PERCENTAGE_USER_EMAIL = "tracker_battery_percentage_user_email";

    public const EXCEEDING_SPEED_LIMIT_USER_WEB = "exceeding_speed_limit_user_web";
    public const EXCEEDING_SPEED_LIMIT_USER_SMS = "exceeding_speed_limit_user_sms";
    public const EXCEEDING_SPEED_LIMIT_USER_MOBILE = "exceeding_speed_limit_user_mobile";
    public const EXCEEDING_SPEED_LIMIT_USER_EMAIL = "exceeding_speed_limit_user_email";

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string')]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string')]
    private $type;

    /**
     * @var array
     */
    #[ORM\Column(name: 'body', type: 'json')]
    private $body;

    /**
     * @var Transport
     */
    #[ORM\ManyToOne(targetEntity: 'Transport')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id')]
    private $transport;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param array $body
     * @return $this
     */
    public function setBody(array $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return Transport
     */
    public function getTransport(): Transport
    {
        return $this->transport;
    }

    /**
     * @param Transport $transport
     * @return $this
     */
    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;

        return $this;
    }

    public function getBodySchema(): array
    {
        return self::TEMPLATE_BODY[$this->getTransport()->getAlias()];
    }

    public function getBodyTranslateKeys()
    {
        $body = [];
        foreach ($this->getBodySchema() as $key) {
            $body[$key] = $this->getName() . '.' . $key;
        }

        return $body;
    }
}
