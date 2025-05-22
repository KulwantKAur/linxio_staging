<?php

namespace App\Enums;

class EntityHistoryTypes
{
    public const CLIENT_STATUS = 'client.status';
    public const CLIENT_CREATED = 'client.created';
    public const CLIENT_UPDATED = 'client.updated';
    public const CLIENT_CONTRACT_CHANGED = 'client.contract.changed';

    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const USER_ARCHIVED = 'user.archived';
    public const USER_STATUS = 'user.status';
    public const USER_REFRESH_TOKEN = 'user.token.refresh';

    public const USER_LAST_LOGIN = 'user.last_login';

    public const DEVICE_CREATED = 'device.created';
    public const DEVICE_UPDATED = 'device.updated';
    public const DEVICE_DELETED = 'device.deleted';
    public const DEVICE_STATUS = 'device.status';
    public const DEVICE_INSTALLED = 'device.installed';
    public const DEVICE_UNINSTALLED = 'device.uninstalled';
    public const DEVICE_CONTRACT_CHANGED = 'device.contract.changed';
    public const DEVICE_PHONE_CHANGED = 'device.phone.changed';
    public const DEVICE_UNAVAILABLE = 'device.unavailable';
    public const DEVICE_DEACTIVATED = 'device.deactivated';
    public const DEVICE_FIELDS = 'device.fields';

    public const VEHICLE_CREATED = 'vehicle.created';
    public const VEHICLE_UPDATED = 'vehicle.updated';
    public const VEHICLE_DELETED = 'vehicle.deleted';
    public const VEHICLE_STATUS = 'vehicle.status';

    public const VEHICLE_GROUP_CREATED = 'vehicleGroup.created';
    public const VEHICLE_GROUP_UPDATED = 'vehicleGroup.updated';
    public const VEHICLE_GROUP_DELETED = 'vehicleGroup.deleted';
    public const VEHICLE_GROUP_STATUS = 'vehicleGroup.status';

    public const DEPOT_CREATED = 'depot.created';
    public const DEPOT_UPDATED = 'depot.updated';
    public const DEPOT_DELETED = 'depot.deleted';

    public const REMINDER_CREATED = 'reminder.created';
    public const REMINDER_UPDATED = 'reminder.updated';
    public const REMINDER_DELETED = 'reminder.deleted';

    public const SERVICE_RECORD_CREATED = 'serviceRecord.created';
    public const SERVICE_RECORD_UPDATED = 'serviceRecord.updated';
    public const SERVICE_RECORD_DELETED = 'serviceRecord.deleted';

    public const REPAIR_CREATED = 'repair.created';
    public const REPAIR_UPDATED = 'repair.updated';
    public const REPAIR_DELETED = 'repair.deleted';

    public const AREA_CREATED = 'area.created';
    public const AREA_UPDATED = 'area.updated';
    public const AREA_DELETED = 'area.deleted';
    public const AREA_STATUS = 'area.status';

    public const AREA_GROUP_CREATED = 'areaGroup.created';
    public const AREA_GROUP_UPDATED = 'areaGroup.updated';
    public const AREA_GROUP_DELETED = 'areaGroup.deleted';

    public const REMINDER_CATEGORY_CREATED = 'reminderCategory.created';
    public const REMINDER_CATEGORY_UPDATED = 'reminderCategory.updated';
    public const REMINDER_CATEGORY_DELETED = 'reminderCategory.deleted';

    public const USER_GROUP_CREATED = 'userGroup.created';
    public const USER_GROUP_UPDATED = 'userGroup.updated';
    public const USER_GROUP_DELETED = 'userGroup.deleted';

    public const DIGITAL_FORM_CREATED = 'digitalForm.created';
    public const DIGITAL_FORM_DELETED = 'digitalForm.deleted';
    public const DIGITAL_FORM_EDITED = 'digitalForm.edited';
    public const DIGITAL_FORM_RESTORED = 'digitalForm.restored';

    public const ACKNOWLEDGE_CREATED = 'acknowledge.created';
    public const ACKNOWLEDGE_UPDATED = 'acknowledge.updated';

    public const RESELLER_CREATED = 'reseller.created';
    public const RESELLER_UPDATED = 'reseller.created';
    public const RESELLER_DELETED = 'reseller.created';
    public const RESELLER_STATUS = 'reseller.status';

    public const DEVICE_SENSOR_INSTALLED = 'deviceSensor.installed';
    public const DEVICE_SENSOR_UNINSTALLED = 'deviceSensor.uninstalled';

    public const SENSOR_CREATED = 'sensor.created';
    public const SENSOR_UPDATED = 'sensor.updated';
    public const SENSOR_DELETED = 'sensor.deleted';

    public const ASSET_CREATED = 'asset.created';
    public const ASSET_UPDATED = 'asset.updated';
    public const ASSET_DELETED = 'asset.deleted';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getAll()
    {
        $class = new \ReflectionClass(__CLASS__);

        return $class->getConstants();
    }
}
