<?php

namespace App\Service\Notification\Placeholder\Factory;

use App\Entity\Notification\Event;
use App\Service\Notification\Placeholder\EntityPlaceholder\AreaHistoryEntity\VehicleEnteredAreaPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\AreaHistoryEntity\VehicleLeftAreaPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\AreaHistoryEntity\VehicleOverSpeedingInsideAreaPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\AssetEntity\AssetCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\AssetEntity\AssetDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\AssetEntity\AssetMissedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity\ClientBlockedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity\ClientCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity\ClientDemoExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity\ClientIntegrationEnabledPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceContractExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceInStockPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceOfflinePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceReplacedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity\DeviceUnavailablePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DigitalFormAnswerEntity\DigitalFormWithFailPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentEntity\AssetDocumentDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentEntity\DocumentDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentEntity\DriverDocumentDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\AssetDocumentExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\AssetDocumentExpireSoonPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\AssetDocumentRecordAddedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DocumentExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DocumentExpireSoonPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DocumentRecordAddedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DriverDocumentExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DriverDocumentExpireSoonPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity\DriverDocumentRecordAddedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\IdlingEntity\VehicleExcessIdlingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\InvoiceCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\InvoiceOverdueBlockedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\InvoiceOverduePartiallyBlockedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\InvoiceOverduePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\PaymentFailedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\PaymentSuccessfulPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\StripePaymentFailedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\StripePaymentMethodMissingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\StripePaymentSuccessfulPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\XeroInvoiceCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\XeroInvoiceCreationErrorPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\XeroPaymentCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity\XeroPaymentCreationErrorPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity\ServiceReminderDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity\ServiceReminderDonePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity\ServiceReminderExpiredPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity\ServiceReminderSoonPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\RouteEntity\DigitalFormIsNotCompletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ServiceRecordEntity\ServiceRecordAddedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\ServiceRecordEntity\ServiceRepairAddedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TeamEntity\StripeIntegrationErrorPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TeamEntity\XeroIntegrationErrorPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerAuthUnknownEntity\TrackerAuthUnknownPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\ExceedingSpeedLimitPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\PanicButtonPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\TrackerAccidentHappenedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\TrackerBatteryPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\TrackerJammerStartedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\TrackerVoltagePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleDrivingWithoutDriverPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleLongDrivingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleLongStandingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleMovingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleOverSpeedingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity\VehicleTowingPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryIOEntity\TrackerHistoryIOPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity\SensorBatteryLevelPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity\SensorHumidityPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity\SensorLightPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity\SensorStatusPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity\SensorTemperaturePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AccessLevelChangedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AdminUserBlockedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AdminUserChangedNamePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AdminUserCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AdminUserDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\AdminUserPasswordResetPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\LoginAsClientPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\LoginAsUserPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\UserBlockedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\UserChangedNamePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\UserCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\UserDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity\UserPasswordResetPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleChangedModelPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleChangedRegNoPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleCreatedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleDeletedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleOfflinePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleOnlinePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleReassignedPlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity\VehicleUnavailablePlaceholder;
use App\Service\Notification\Placeholder\EntityPlaceholder\VehicleOdometerEntity\VehicleOdometerPlaceholder;
use App\Service\Notification\Placeholder\Exception\UndefinedPlaceholderByEventException;
use App\Service\Notification\Placeholder\Interfaces\PlaceholderInterface;

class PlaceholderFactory
{
    protected static $availablePlaceholderByEvent = [
        Event::ADMIN_USER_BLOCKED => AdminUserBlockedPlaceholder::class,
        Event::ADMIN_USER_CHANGED_NAME => AdminUserChangedNamePlaceholder::class,
        Event::ADMIN_USER_CREATED => AdminUserCreatedPlaceholder::class,
        Event::ADMIN_USER_DELETED => AdminUserDeletedPlaceholder::class,
        Event::ADMIN_USER_PWD_RESET => AdminUserPasswordResetPlaceholder::class,
        Event::LOGIN_AS_CLIENT => LoginAsClientPlaceholder::class,
        Event::LOGIN_AS_USER => LoginAsUserPlaceholder::class,
        Event::USER_BLOCKED => UserBlockedPlaceholder::class,
        Event::USER_CHANGED_NAME => UserChangedNamePlaceholder::class,
        Event::USER_CREATED => UserCreatedPlaceholder::class,
        Event::USER_CREATED_SYSTEM => UserCreatedPlaceholder::class,
        Event::USER_DELETED => UserDeletedPlaceholder::class,
        Event::USER_PWD_RESET => UserPasswordResetPlaceholder::class,
        Event::CLIENT_BLOCKED => ClientBlockedPlaceholder::class,
        Event::CLIENT_CREATED => ClientCreatedPlaceholder::class,
        Event::CLIENT_DEMO_EXPIRED => ClientDemoExpiredPlaceholder::class,

        Event::DEVICE_DELETED => DeviceDeletedPlaceholder::class,
        Event::DEVICE_IN_STOCK => DeviceInStockPlaceholder::class,
        Event::DEVICE_OFFLINE => DeviceOfflinePlaceholder::class,
        Event::DEVICE_UNAVAILABLE => DeviceUnavailablePlaceholder::class,
        Event::DEVICE_UNKNOWN_DETECTED => TrackerAuthUnknownPlaceholder::class,
        Event::DEVICE_REPLACED => DeviceReplacedPlaceholder::class,

        Event::DOCUMENT_DELETED => DocumentDeletedPlaceholder::class,
        Event::DRIVER_DOCUMENT_DELETED => DriverDocumentDeletedPlaceholder::class,
        Event::DOCUMENT_EXPIRED => DocumentExpiredPlaceholder::class,
        Event::DOCUMENT_EXPIRE_SOON => DocumentExpireSoonPlaceholder::class,
        Event::DOCUMENT_RECORD_ADDED => DocumentRecordAddedPlaceholder::class,
        Event::DRIVER_DOCUMENT_EXPIRED => DriverDocumentExpiredPlaceholder::class,
        Event::DRIVER_DOCUMENT_EXPIRE_SOON => DriverDocumentExpireSoonPlaceholder::class,
        Event::DRIVER_DOCUMENT_RECORD_ADDED => DriverDocumentRecordAddedPlaceholder::class,
        Event::SERVICE_REMINDER_DELETED => ServiceReminderDeletedPlaceholder::class,
        Event::SERVICE_REMINDER_DONE => ServiceReminderDonePlaceholder::class,
        Event::SERVICE_REMINDER_EXPIRED => ServiceReminderExpiredPlaceholder::class,
        Event::SERVICE_REMINDER_SOON => ServiceReminderSoonPlaceholder::class,
        Event::SERVICE_RECORD_ADDED => ServiceRecordAddedPlaceholder::class,
        Event::SERVICE_REPAIR_ADDED => ServiceRepairAddedPlaceholder::class,

        Event::VEHICLE_CREATED => VehicleCreatedPlaceholder::class,
        Event::VEHICLE_DELETED => VehicleDeletedPlaceholder::class,
        Event::VEHICLE_OFFLINE => VehicleOfflinePlaceholder::class,
        Event::VEHICLE_REASSIGNED => VehicleReassignedPlaceholder::class,
        Event::VEHICLE_UNAVAILABLE => VehicleUnavailablePlaceholder::class,
        Event::VEHICLE_ONLINE => VehicleOnlinePlaceholder::class,
        Event::VEHICLE_CHANGED_MODEL => VehicleChangedModelPlaceholder::class,
        Event::VEHICLE_CHANGED_REGNO => VehicleChangedRegNoPlaceholder::class,
        Event::VEHICLE_EXCESSING_IDLING => VehicleExcessIdlingPlaceholder::class,
        Event::VEHICLE_GEOFENCE_ENTER => VehicleEnteredAreaPlaceholder::class,
        Event::VEHICLE_GEOFENCE_LEAVE => VehicleLeftAreaPlaceholder::class,
        Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE => VehicleOverSpeedingInsideAreaPlaceholder::class,
        Event::VEHICLE_DRIVING_WITHOUT_DRIVER => VehicleDrivingWithoutDriverPlaceholder::class,
        Event::VEHICLE_LONG_DRIVING => VehicleLongDrivingPlaceholder::class,
        Event::VEHICLE_LONG_STANDING => VehicleLongStandingPlaceholder::class,
        Event::VEHICLE_MOVING => VehicleMovingPlaceholder::class,
        Event::VEHICLE_OVERSPEEDING => VehicleOverSpeedingPlaceholder::class,
        Event::VEHICLE_TOWING_EVENT => VehicleTowingPlaceholder::class,
        Event::PANIC_BUTTON => PanicButtonPlaceholder::class,
        Event::TRACKER_VOLTAGE => TrackerVoltagePlaceholder::class,
        Event::TRACKER_BATTERY_PERCENTAGE => TrackerBatteryPlaceholder::class,
        Event::ODOMETER_CORRECTED => VehicleOdometerPlaceholder::class,
        Event::DIGITAL_FORM_IS_NOT_COMPLETED => DigitalFormIsNotCompletedPlaceholder::class,
        Event::DIGITAL_FORM_WITH_FAIL => DigitalFormWithFailPlaceholder::class,
        Event::SENSOR_IO_STATUS => TrackerHistoryIOPlaceholder::class,
        Event::SENSOR_BATTERY_LEVEL => SensorBatteryLevelPlaceholder::class,
        Event::SENSOR_HUMIDITY => SensorHumidityPlaceholder::class,
        Event::SENSOR_LIGHT => SensorLightPlaceholder::class,
        Event::SENSOR_STATUS => SensorStatusPlaceholder::class,
        Event::SENSOR_TEMPERATURE => SensorTemperaturePlaceholder::class,
        Event::TRACKER_JAMMER_STARTED_ALARM => TrackerJammerStartedPlaceholder::class,
        Event::TRACKER_ACCIDENT_HAPPENED_ALARM => TrackerAccidentHappenedPlaceholder::class,
        Event::ASSET_CREATED => AssetCreatedPlaceholder::class,
        Event::ASSET_DELETED => AssetDeletedPlaceholder::class,
        Event::ASSET_MISSED => AssetMissedPlaceholder::class,
        Event::ASSET_DOCUMENT_EXPIRED => AssetDocumentExpiredPlaceholder::class,
        Event::ASSET_DOCUMENT_EXPIRE_SOON => AssetDocumentExpireSoonPlaceholder::class,
        Event::ASSET_DOCUMENT_RECORD_ADDED => AssetDocumentRecordAddedPlaceholder::class,
        Event::ASSET_DOCUMENT_DELETED => AssetDocumentDeletedPlaceholder::class,

        Event::STRIPE_INTEGRATION_ERROR => StripeIntegrationErrorPlaceholder::class,
        Event::STRIPE_PAYMENT_FAILED => StripePaymentFailedPlaceholder::class,
        Event::STRIPE_PAYMENT_SUCCESSFUL => StripePaymentSuccessfulPlaceholder::class,
        Event::XERO_INTEGRATION_ERROR => XeroIntegrationErrorPlaceholder::class,
        Event::XERO_INVOICE_CREATION_ERROR => XeroInvoiceCreationErrorPlaceholder::class,
        Event::XERO_INVOICE_CREATED => XeroInvoiceCreatedPlaceholder::class,
        Event::XERO_PAYMENT_CREATION_ERROR => XeroPaymentCreationErrorPlaceholder::class,
        Event::XERO_PAYMENT_CREATED => XeroPaymentCreatedPlaceholder::class,

        Event::INVOICE_CREATED => InvoiceCreatedPlaceholder::class,
        Event::PAYMENT_FAILED => PaymentFailedPlaceholder::class,
        Event::PAYMENT_SUCCESSFUL => PaymentSuccessfulPlaceholder::class,

        Event::INVOICE_OVERDUE => InvoiceOverduePlaceholder::class,
        Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED => InvoiceOverduePartiallyBlockedPlaceholder::class,
        Event::INVOICE_OVERDUE_BLOCKED => InvoiceOverdueBlockedPlaceholder::class,

        Event::DEVICE_CONTRACT_EXPIRED => DeviceContractExpiredPlaceholder::class,
        Event::EXCEEDING_SPEED_LIMIT => ExceedingSpeedLimitPlaceholder::class,
        Event::INTEGRATION_ENABLED => ClientIntegrationEnabledPlaceholder::class,
        Event::ACCESS_LEVEL_CHANGED => AccessLevelChangedPlaceholder::class,
    ];

    /**
     * @param Event $event
     * @return PlaceholderInterface
     * @throws UndefinedPlaceholderByEventException
     */
    public function getInstance(Event $event): PlaceholderInterface
    {
        $eventName = $event->getName();

        if (!array_key_exists($eventName, self::$availablePlaceholderByEvent)) {
            throw new UndefinedPlaceholderByEventException(
                sprintf(
                    'Undefined placeholder by event: "%s".',
                    $eventName
                )
            );
        }

        $placeholderClass = self::$availablePlaceholderByEvent[$eventName];

        return (new $placeholderClass($event));
    }
}
