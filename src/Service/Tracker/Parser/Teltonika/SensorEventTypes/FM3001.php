<?php

namespace App\Service\Tracker\Parser\Teltonika\SensorEventTypes;

use App\Entity\DeviceModel;

/*
 * https://wiki.teltonika-gps.com/view/FMB_AVL_ID
 */
class FM3001 extends BaseType
{
    public static $model = DeviceModel::TELTONIKA_FM3001;

    const IGNITION_ID = 239;
    const MOVEMENT_ID = 240;
    const DATA_MODE_ID = 80;
    const GSM_SIGNAL_ID = 21;
    const SLEEP_MODE_ID = 200;
    const GNSS_STATUS_ID = 69;
    const GNSS_PDOP_ID = 181;
    const GNSS_HDOP_ID = 182;
    const EXTERNAL_VOLTAGE_ID = 66;
    const SPEED_ID = 24;
    const GSM_CELL_ID_ID = 205;
    const GSM_AREA_CODE_ID = 206;
    const BATTERY_VOLTAGE_ID = 67;
    const ACTIVE_GSM_OPERATOR_ID = 241;
    const TRIP_ODOMETER_ID = 199;
    const TOTAL_ODOMETER_ID = 16;
    const FUEL_USED_GPS_ID = 12;
    const FUEL_RATE_GPS_ID = 13;
    const AXIS_X_ID = 17;
    const AXIS_Y_ID = 18;
    const AXIS_Z_ID = 19;
    const ICCID1_ID = 11;
    const SD_STATUS_ID = 10;
    const ECO_SCORE_ID = 15;
    const BATTERY_LEVEL_ID = 113;
    const USER_ID_ID = 238;
    const BLE_1_TEMPERATURE_ID = 25;
    const BLE_2_TEMPERATURE_ID = 26;
    const BLE_3_TEMPERATURE_ID = 27;
    const BLE_4_TEMPERATURE_ID = 28;
    const BLE_1_BATTERY_VOLTAGE_ID = 29;
    const BLE_2_BATTERY_VOLTAGE_ID = 20;
    const BLE_3_BATTERY_VOLTAGE_ID = 22;
    const BLE_4_BATTERY_VOLTAGE_ID = 23;
    const BLE_1_HUMIDITY_ID = 86;
    const BLE_2_HUMIDITY_ID = 104;
    const BLE_3_HUMIDITY_ID = 106;
    const BLE_4_HUMIDITY_ID = 108;
    const NETWORK_TYPE_ID = 237;
    const VIN_ID = 256;
    const NUMBER_OF_DTC_ID = 30;
    const ENGINE_LOAD_ID = 31;
    const COOLANT_TEMPERATURE_ID = 32;
    const SHORT_FUEL_TRIM_ID = 33;
    const FUEL_PRESSURE_ID = 34;
    const INTAKE_MAP_ID = 35;
    const ENGINE_RPM_ID = 36;
    const VEHICLE_SPEED_ID = 37;
    const TIMING_ADVANCE_ID = 38;
    const INTAKE_AIR_TEMPERATURE_ID = 39;
    const MAF_ID = 40;
    const THROTTLE_POSITION_ID = 41;
    const RUN_TIME_SINCE_ENGINE_START_ID = 42; // seconds
    const DISTANCE_TRAVELED_MIL_ON_ID = 43;
    const RELATIVE_FUEL_RAIL_PRESSURE_ID = 44;
    const DIRECT_FUEL_RAIL_PRESSURE_ID = 45;
    const COMMANDED_EGR_ID = 46;
    const EGR_ERROR_ID = 47;
    const FUEL_LEVEL_ID = 48;
    const DISTANCE_SINCE_CODES_CLEAR_ID = 49;
    const BAROMETRIC_PRESSURE_ID = 50;
    const CONTROL_MODULE_VOLTAGE_ID = 51;
    const ABSOLUTE_LOAD_VALUE_ID = 52;
    const AMBIENT_AIR_TEMPERATURE_ID = 53;
    const TIME_RUN_WITH_MIL_ON_ID = 54;
    const TIME_SINCE_CODES_CLEARED_ID = 55;
    const ABSOLUTE_FUEL_RAIL_PRESSURE_ID = 56;
    const HYBRID_BATTERY_PACK_LIFE_ID = 57;
    const ENGINE_OIL_TEMPERATURE_ID = 58;
    const FUEL_INJECTION_TIMING_ID = 59;
    const FUEL_RATE_ID = 60;
    const IBUTTON_ID_ID = 78;
    const GEOFENCE_ZONE_01_ID = 155;
    const GEOFENCE_ZONE_02_ID = 156;
    const GEOFENCE_ZONE_03_ID = 157;
    const GEOFENCE_ZONE_04_ID = 158;
    const GEOFENCE_ZONE_05_ID = 159;
    const GEOFENCE_ZONE_06_ID = 61;
    const GEOFENCE_ZONE_07_ID = 62;
    const GEOFENCE_ZONE_08_ID = 63;
    const GEOFENCE_ZONE_09_ID = 64;
    const GEOFENCE_ZONE_10_ID = 65;
    const GEOFENCE_ZONE_11_ID = 70;
    const GEOFENCE_ZONE_12_ID = 88;
    const GEOFENCE_ZONE_13_ID = 91;
    const GEOFENCE_ZONE_14_ID = 92;
    const GEOFENCE_ZONE_15_ID = 93;
    const GEOFENCE_ZONE_16_ID = 94;
    const GEOFENCE_ZONE_17_ID = 95;
    const GEOFENCE_ZONE_18_ID = 96;
    const GEOFENCE_ZONE_19_ID = 97;
    const GEOFENCE_ZONE_20_ID = 98;
    const GEOFENCE_ZONE_21_ID = 99;
    const GEOFENCE_ZONE_22_ID = 153;
    const GEOFENCE_ZONE_23_ID = 154;
    const GEOFENCE_ZONE_24_ID = 190;
    const GEOFENCE_ZONE_25_ID = 191;
    const GEOFENCE_ZONE_26_ID = 192;
    const GEOFENCE_ZONE_27_ID = 193;
    const GEOFENCE_ZONE_28_ID = 194;
    const GEOFENCE_ZONE_29_ID = 195;
    const GEOFENCE_ZONE_30_ID = 196;
    const GEOFENCE_ZONE_31_ID = 197;
    const GEOFENCE_ZONE_32_ID = 198;
    const GEOFENCE_ZONE_33_ID = 208;
    const GEOFENCE_ZONE_34_ID = 209;
    const GEOFENCE_ZONE_35_ID = 216;
    const GEOFENCE_ZONE_36_ID = 217;
    const GEOFENCE_ZONE_37_ID = 218;
    const GEOFENCE_ZONE_38_ID = 219;
    const GEOFENCE_ZONE_39_ID = 220;
    const GEOFENCE_ZONE_40_ID = 221;
    const GEOFENCE_ZONE_41_ID = 222;
    const GEOFENCE_ZONE_42_ID = 223;
    const GEOFENCE_ZONE_43_ID = 224;
    const GEOFENCE_ZONE_44_ID = 225;
    const GEOFENCE_ZONE_45_ID = 226;
    const GEOFENCE_ZONE_46_ID = 227;
    const GEOFENCE_ZONE_47_ID = 228;
    const GEOFENCE_ZONE_48_ID = 229;
    const GEOFENCE_ZONE_49_ID = 230;
    const GEOFENCE_ZONE_50_ID = 231;
    const AUTO_GEOFENCE_ID = 175;
    const TRIP_ID = 250;
    const OVER_SPEEDING_ID = 255;
    const IDLING_ID = 251;
    const GREEN_DRIVING_TYPE_ID = 253;
    const TOWING_ID = 246;
    const CRASH_DETECTION_ID = 247;
    const GREEN_DRIVING_VALUE_ID = 254;
    const JAMMING_ID = 249;
    const ICCID2_ID = 14;
    const GREEN_DRIVING_EVENT_DURATION_ID = 243;
    const FAULT_CODES_ID = 281;
    const INSTANT_MOVEMENT_ID = 303;

    public static function getTotalOdometerId()
    {
        return self::TOTAL_ODOMETER_ID;
    }

    public static function getEngineHoursId()
    {
        // @todo: is there some other option to detect `engine on` time?
        return self::RUN_TIME_SINCE_ENGINE_START_ID;
    }

    public static function getTemperatureLevelId()
    {
        return self::ENGINE_OIL_TEMPERATURE_ID;
    }

    public static function getGSMSignalId()
    {
        return self::GSM_SIGNAL_ID;
    }

    public static function getBatteryVoltageId()
    {
        return self::BATTERY_VOLTAGE_ID;
    }

    public static function getExternalVoltageId()
    {
        return self::EXTERNAL_VOLTAGE_ID;
    }

    public static function getIgnitionId()
    {
        return self::IGNITION_ID;
    }

    public static function getMovementId()
    {
        return self::MOVEMENT_ID;
    }

    public static function getIButtonId()
    {
        return self::IBUTTON_ID_ID;
    }

    /**
     * @param $geofenceValue
     * @return string
     */
    public static function getGeofenceMessageByValue($geofenceValue)
    {
        switch ($geofenceValue) {
            case 3:
                $msg = 'over speeding start';
                break;
            case 2:
                $msg = 'over speeding end';
                break;
            case 1:
                $msg = 'target entered zone';
                break;
            case 0:
                $msg = 'target left zone';
                break;
            default:
                $msg = 'unknown value';
                break;
        }

        return $msg;
    }
}
