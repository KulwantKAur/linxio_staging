<?php

namespace App\Repository\Billing;

class ClientBilling
{
    const activeVehicleTrackers = 'ceil(COALESCE(duva.active_device_uv_count, 0)::decimal/:period * 10) / 10';
    const deactivatedVehicleTrackers = 'ceil(COALESCE(duvia.inactive_device_uv_count, 0)::decimal/:period * 10) / 10';
    const activePersonalTrackers = 'ceil(COALESCE(dupa.active_device_up_count, 0)::decimal/:period * 10) / 10';
    const deactivatedPersonalTrackers = 'ceil(COALESCE(dupia.inactive_device_up_count, 0)::decimal/:period * 10) / 10';
    const activeAssetTrackers = 'ceil(COALESCE(duaa.active_device_ua_count, 0)::decimal /:period * 10) / 10';
    const deactivatedAssetTrackers = 'ceil(COALESCE(duaia.inactive_device_ua_count, 0)::decimal /:period * 10) / 10';
    const activeSatelliteTrackers = 'ceil(COALESCE(dusa.active_device_us_count, 0)::decimal /:period * 10) / 10';
    const deactivatedSatelliteTrackers = 'ceil(COALESCE(dusia.inactive_device_us_count, 0)::decimal /:period * 10) / 10';
    const activeVehicles = 'ceil(COALESCE(vva.active_vehicles, 0)::decimal /:period * 10) / 10';
    const virtualVehicles = 'ceil(COALESCE(vv.virtual_vehicles, 0)::decimal /:period * 10) / 10';
    const archivedVehicles = 'ceil(COALESCE(vvar.archived_vehicles, 0)::decimal /:period * 10) / 10';
    const deletedVehicles = 'COALESCE(vvd.deleted_vehicles, 0)';
    const activeSensors = 'COALESCE(sa.all_sensors - COALESCE(sd.deleted_sensors, 0), 0)';
    const archivedSensors = 'ceil(COALESCE(0, 0)::decimal /:period * 10) / 10';
    const deletedSensors = 'COALESCE(sd.deleted_sensors, 0)';

    const activeVehicleTrackersAlias = 'active_vehicle_trackers';
    const deactivatedVehicleTrackersAlias = 'deactivated_vehicle_trackers';
    const activePersonalTrackersAlias = 'active_personal_trackers';
    const deactivatedPersonalTrackersAlias = 'deactivated_personal_trackers';
    const activeAssetTrackersAlias = 'active_asset_trackers';
    const deactivatedAssetTrackersAlias = 'deactivated_asset_trackers';
    const activeSatelliteTrackersAlias = 'active_satellite_trackers';
    const deactivatedSatelliteTrackersAlias = 'deactivated_satellite_trackers';
    const activeVehiclesAlias = 'active_vehicles';
    const virtualVehiclesAlias = 'virtual_vehicles';
    const archivedVehiclesAlias = 'archived_vehicles';
    const deletedVehiclesAlias = 'deleted_vehicles';
    const activeSensorsAlias = 'active_sensors';
    const archivedSensorsAlias = 'archived_sensors';
    const deletedSensorsAlias = 'deleted_sensors';
    const active_vehicle_sign_post = 'active_vehicle_sign_post';

    const activeVehicleTrackersPrice = 'active_vehicle_trackers_price';
    const activeVehicleTrackersTotal = 'active_vehicle_trackers_total';
    const activeVehicleTrackersTotalSum = 'active_vehicle_trackers_total_sum';
    const deactivatedVehicleTrackersPrice = 'deactivated_vehicle_trackers_price';
    const deactivatedVehicleTrackersTotal = 'deactivated_vehicle_trackers_total';
    const deactivatedVehicleTrackersTotalSum = 'deactivated_vehicle_trackers_total_sum';
    const activePersonalTrackersPrice = 'active_personal_trackers_price';
    const activePersonalTrackersTotal = 'active_personal_trackers_total';
    const activePersonalTrackersTotalSum = 'active_personal_trackers_total_sum';
    const deactivatedPersonalTrackersPrice = 'deactivated_personal_trackers_price';
    const deactivatedPersonalTrackersTotal = 'deactivated_personal_trackers_total';
    const deactivatedPersonalTrackersTotalSum = 'deactivated_personal_trackers_total_sum';
    const activeAssetTrackersPrice = 'active_asset_trackers_price';
    const activeAssetTrackersTotal = 'active_asset_trackers_total';
    const activeAssetTrackersTotalSum = 'active_asset_trackers_total_sum';
    const deactivatedAssetTrackersPrice = 'deactivated_asset_trackers_price';
    const deactivatedAssetTrackersTotal = 'deactivated_asset_trackers_total';
    const deactivatedAssetTrackersTotalSum = 'deactivated_asset_trackers_total_sum';
    const activeSatelliteTrackersPrice = 'active_satellite_trackers_price';
    const activeSatelliteTrackersTotal = 'active_satellite_trackers_total';
    const activeSatelliteTrackersTotalSum = 'active_satellite_trackers_total_sum';
    const deactivatedSatelliteTrackersPrice = 'deactivated_satellite_trackers_price';
    const deactivatedSatelliteTrackersTotal = 'deactivated_satellite_trackers_total';
    const deactivatedSatelliteTrackersTotalSum = 'deactivated_satellite_trackers_total_sum';
    const activeVehiclesPrice = 'active_vehicles_price';
    const activeVehiclesTotal = 'active_vehicles_total';
    const activeVehiclesTotalSum = 'active_vehicles_total_sum';
    const virtualVehiclesPrice = 'virtual_vehicles_price';
    const virtualVehiclesTotal = 'virtual_vehicles_total';
    const virtualVehiclesTotalSum = 'virtual_vehicles_total_sum';
    const archivedVehiclesPrice = 'archived_vehicles_price';
    const archivedVehiclesTotal = 'archived_vehicles_total';
    const archivedVehiclesTotalSum = 'archived_vehicles_total_sum';
    const deletedVehiclesPrice = 'deleted_vehicles_price';
    const deletedVehiclesTotal = 'deleted_vehicles_total';
    const deletedVehiclesTotalSum = 'deleted_vehicles_total_sum';
    const activeSensorsPrice = 'active_sensors_price';
    const activeSensorsTotal = 'active_sensors_total';
    const activeSensorsTotalSum = 'active_sensors_total_sum';
    const archivedSensorsPrice = 'archived_sensors_price';
    const archivedSensorsTotal = 'archived_sensors_total';
    const archivedSensorsTotalSum = 'archived_sensors_total_sum';
    const deletedSensorsPrice = 'deleted_sensors_price';
    const deletedSensorsTotal = 'deleted_sensors_total';
    const deletedSensorsTotalSum = 'deleted_sensors_total_sum';
    const allClientsTotalSum = 'all_clients_total_sum';
    const activeVehicleSignPostPrice = 'active_vehicle_sign_post_price';
    const activeVehicleSignPostTotal = 'active_vehicle_sign_post_total';
    const activeVehicleSignPostTotalSum = 'active_vehicle_sign_post_total_sum';

    const activeVehicleTrackersTotalSelect = 'COALESCE(' . ClientBilling::activeVehicleTrackers . ' * bp.device_vehicle_active, 0)';
    const deactivatedVehicleTrackersTotalSelect = 'COALESCE(' . ClientBilling::deactivatedVehicleTrackers . ' * bp.device_vehicle_deactivated, 0)';
    const activePersonalTrackersTotalSelect = 'COALESCE(' . ClientBilling::activePersonalTrackers . ' * bp.device_personal_active, 0)';
    const deactivatedPersonalTrackersTotalSelect = 'COALESCE(' . ClientBilling::deactivatedPersonalTrackers . ' * bp.device_personal_deactivated, 0)';
    const activeAssetTrackersTotalSelect = 'COALESCE(' . ClientBilling::activeAssetTrackers . ' * bp.device_asset_active, 0)';
    const deactivatedAssetTrackersTotalSelect = 'COALESCE(' . ClientBilling::deactivatedAssetTrackers . ' * bp.device_asset_deactivated, 0)';
    const activeSatelliteTrackersTotalSelect = 'COALESCE(' . ClientBilling::activeSatelliteTrackers . ' * bp.device_satellite_active, 0)';
    const deactivatedSatelliteTrackersTotalSelect = 'COALESCE(' . ClientBilling::deactivatedSatelliteTrackers . ' * bp.device_satellite_deactivated, 0)';
    const activeVehiclesTotalSelect = '0';
    const virtualVehiclesTotalSelect = 'COALESCE(' . ClientBilling::virtualVehicles . ' * bp.vehicle_virtual, 0)';
    const archivedVehiclesTotalSelect = 'COALESCE(' . ClientBilling::archivedVehicles . ' * bp.vehicle_archived, 0)';
    const deletedVehiclesTotalSelect = '0';
    const activeSensorsTotalSelect = 'COALESCE(' . ClientBilling::activeSensors . ' * temp_sensor, 0)';
    const archivedSensorsTotalSelect = 'COALESCE(' . ClientBilling::archivedSensors . ' * bp.sensor_archived, 0)';
    const deletedSensorsTotalSelect = '0';
    const activeVehiclesSignPostTotalSelect = 'COALESCE(' . ClientBilling::activeVehicles . ' * bp.sign_post_vehicle, 0)';

    public static function getTotalSumFields(): array
    {
        return [
            self::activeVehicleTrackersTotalSum,
            self::deactivatedVehicleTrackersTotalSum,
            self::activePersonalTrackersTotalSum,
            self::deactivatedPersonalTrackersTotalSum,
            self::activeAssetTrackersTotalSum,
            self::deactivatedAssetTrackersTotalSum,
            self::activeSatelliteTrackersTotalSum,
            self::deactivatedSatelliteTrackersTotalSum,
            self::activeVehiclesTotalSum,
            self::virtualVehiclesTotalSum,
            self::archivedVehiclesTotalSum,
            self::deletedVehiclesTotalSum,
            self::activeSensorsTotalSum,
            self::archivedSensorsTotalSum,
            self::deletedSensorsTotalSum,
            self::allClientsTotalSum,
        ];
    }

    public static function getAliasFields(): array
    {
        return [
            self::activeVehicleTrackersAlias,
            self::deactivatedVehicleTrackersAlias,
            self::activePersonalTrackersAlias,
            self::deactivatedPersonalTrackersAlias,
            self::activeAssetTrackersAlias,
            self::deactivatedAssetTrackersAlias,
            self::activeSatelliteTrackersAlias,
            self::deactivatedSatelliteTrackersAlias,
            self::activeVehiclesAlias,
            self::virtualVehiclesAlias,
            self::archivedVehiclesAlias,
            self::deletedVehiclesAlias,
            self::activeSensorsAlias,
            self::archivedSensorsAlias,
            self::deletedSensorsAlias,
        ];
    }
}
