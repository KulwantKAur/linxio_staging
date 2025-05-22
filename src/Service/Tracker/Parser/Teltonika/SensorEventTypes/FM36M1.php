<?php

namespace App\Service\Tracker\Parser\Teltonika\SensorEventTypes;

use App\Entity\DeviceModel;

/*
 * Current model status: end of life
 * https://wiki.teltonika-gps.com/view/FM36M1_AVL_ID
 */
class FM36M1 extends BaseType
{
    public static $model = DeviceModel::TELTONIKA_FM36M1;

    const DIGITAL_INPUT_STATUS_1_ID = 1;
    const DIGITAL_INPUT_STATUS_2_ID = 2;
    const DIGITAL_INPUT_STATUS_3_ID = 3;
    const DIGITAL_INPUT_STATUS_4_ID = 4;
    const ANALOG_INPUT_1_ID = 9;
    const ANALOG_INPUT_2_ID = 10;
    const GSM_LEVEL_ID = 21;
    const SPEED_ID = 24;
    const EXTERNAL_POWER_VOLTAGE_ID = 66;
    const BATTERY_VOLTAGE_ID = 67;
    const BATTERY_CURRENT_ID = 68;
    const GNSS_STATUS_ID = 69;
    const DALLAS_TEMPERATURE_1_ID = 72;
    const DALLAS_TEMPERATURE_2_ID = 73;
    const DALLAS_TEMPERATURE_3_ID = 74;
    const DALLAS_TEMPERATURE_SENSOR_ID1_ID = 75;
    const DALLAS_TEMPERATURE_SENSOR_ID2_ID = 76;
    const DALLAS_TEMPERATURE_SENSOR_ID3_ID = 77;
    const IBUTTON_ID_ID = 78;
    const NETWORK_TYPE_ID = 79;
    const WORKING_MODE_ID = 80;
    const CONTINUOUS_ODOMETER_ID = 99;
    const DIGITAL_OUTPUT_1_STATE_ID = 179;
    const DIGITAL_OUTPUT_2_STATE_ID = 180;
    const PDOP_ID = 181;
    const HDOP_ID = 182;
    const ODOMETER_VALUE_VIRTUAL_ODOMETER_ID = 199;
    const DEEP_SLEEP_ID = 200;
    const CELL_ID_ID = 205;
    const AREA_CODE_ID = 206;
    const IGNITION_ID = 239;
    const MOVEMENT_SENSOR_ID = 240;
    const GSM_OPERATOR_CODE_ID = 241;
    const LVCAN_SPEED_ID = 81;
    const LVCAN_ACCELERATOR_PEDAL_POSITION_ID = 82;
    const LVCAN_FUEL_CONSUMED_ID = 83;
    const LVCAN_FUEL_LEVEL_LITERS_ID = 84;
    const LVCAN_ENGINE_RPM_ID = 85;
    const LVCAN_TOTAL_MILEAGE_ID = 87;
    const LVCAN_FUEL_LEVEL_PERCENTAGE_ID = 89;
    const LVCAN_PROGRAM_NUMBER_ID = 100;
    const LVCAN_MODULEID_ID = 101;
    const LVCAN_ENGINE_WORK_TIME_ID = 102;
    const LVCAN_ENGINE_WORK_TIME_COUNTED_ID = 103;
    const LVCAN_TOTAL_MILEAGE2_ID = 104;
    const LVCAN_TOTAL_MILEAGE_COUNTED_ID = 105;
    const LVCAN_FUEL_CONSUMED2_ID = 106;
    const LVCAN_FUEL_CONSUMED_COUNTED_ID = 107;
    const LVCAN_FUEL_LEVEL_PERCENT_ID = 108;
    const LVCAN_FUEL_LEVEL_LITERS2_ID = 109;
    const LVCAN_FUEL_RATE_ID = 110;
    const LVCAN_ADBLUE_LEVEL_PERCENT_ID = 111;
    const LVCAN_ADBLUE_LEVEL_LITERS_ID = 112;
    const LVCAN_ENGINE_RPM2_ID = 113;
    const LVCAN_ENGINE_LOAD_ID = 114;
    const LVCAN_ENGINE_TEMPERATURE_ID = 115;
    const LVCAN_ACCELERATOR_PEDAL_POSITION2_ID = 116;
    const LVCAN_VEHICLE_SPEED_ID = 117;
    const LVCAN_AXLE_1_LOAD_ID = 118;
    const LVCAN_AXLE_2_LOAD_ID = 119;
    const LVCAN_AXLE_3_LOAD_ID = 120;
    const LVCAN_AXLE_4_LOAD_ID = 121;
    const LVCAN_AXLE_5_LOAD_ID = 122;
    const LVCAN_CONTROL_STATE_FLAGS_ID = 123;
    const LVCAN_AGRICULTURAL_MACHINERY_FLAGS_ID = 124;
    const LVCAN_HARVESTING_TIME_ID = 125;
    const LVCAN_AREA_OF_HARVEST_ID = 126;
    const LVCAN_MOWING_EFFICIENCY_ID = 127;
    const LVCAN_GRAIN_MOWN_VOLUME_ID = 128;
    const LVCAN_GRAIN_MOISTURE_ID = 129;
    const LVCAN_HARVESTING_DRUM_RPM_ID = 130;
    const LVCAN_GAP_UNDER_HARVESTING_DRUM_ID = 131;
    const LVCAN_SECURITY_STATE_FLAGS_ID = 132;
    const LVCAN_TACHO_TOTAL_VEHICLE_DISTANCE_ID = 133;
    const LVCAN_TRIP_DISTANCE_ID = 134;
    const LVCAN_TACHO_VEHICLE_SPEED_ID = 135;
    const LVCAN_TACHO_DRIVER_CARD_PRESENCE_ID = 136;
    const LVCAN_DRIVER1_STATES_ID = 137;
    const LVCAN_DRIVER2_STATES_ID = 138;
    const LVCAN_DRIVER1_CONTINUOUS_DRIVING_TIME_ID = 139;
    const LVCAN_DRIVER2_CONTINUOUS_DRIVING_TIME_ID = 140;
    const LVCAN_DRIVER1_CUMULATIVE_BREAK_TIME_ID = 141;
    const LVCAN_DRIVER2_CUMULATIVE_BREAK_TIME_ID = 142;
    const LVCAN_DRIVER1_DURATION_OF_SELECTED_ACTIVITY_ID = 143;
    const LVCAN_DRIVER2_DURATION_OF_SELECTED_ACTIVITY_ID = 144;
    const LVCAN_DRIVER1_CUMULATIVE_DRIVING_TIME_ID = 145;
    const LVCAN_DRIVER2_CUMULATIVE_DRIVING_TIME_ID = 146;
    const LVCAN_DRIVER1_ID_HIGH_ID = 147;
    const LVCAN_DRIVER1_ID_LOW_ID = 148;
    const LVCAN_DRIVER2_ID_HIGH_ID = 149;
    const LVCAN_DRIVER2_ID_LOW_ID = 150;
    const LVCAN_BATTERY_TEMPERATURE_ID = 151;
    const LVCAN_BATTERY_LEVEL_PERCENT_ID = 152;
    const LVCAN_DOOR_STATUS_ID = 90;
    const LVCAN_DTC_ERRORS_ID = 160;
    const LVCAN_SLOPE_OF_ARM_ID = 161;
    const LVCAN_ROTATION_OF_ARM_ID = 162;
    const LVCAN_EJECT_OF_ARM_ID = 163;
    const LVCAN_HORIZONTAL_DIST_ARM_VECHICLE_ID = 164;
    const LVCAN_HEIGHT_ARM_ABOVE_GROUND_ID = 165;
    const LVC_DRILL_RPM_ID = 166;
    const LVC_AMOUNT_OF_SPREAD_SALT_SQUARE_METER_ID = 167;
    const LVC_BATTERY_VOLTAGE_ID = 168;
    const LVC_AMOUNT_SPREAD_FINE_GRAINED_SALT_ID = 169;
    const LVCAN_AMOUNT_SPREAD_COARSE_GRAINED_SALT_ID = 170;
    const LVCAN_AMOUNT_SPREAD_DIMIX_ID = 171;
    const LVCAN_AMOUNT_SPREAD_COARSE_GRAINED_CALC_ID = 172;
    const LVCAN_AMOUNT_SPREAD_CALCIUM_CHLORIDE_ID = 173;
    const LVCAN_AMOUNT_SPREAD_SODIUM_CHLORIDE_ID = 174;
    const LVCAN_AMOUNT_SPREAD_MAGNESIUM_CHLORIDE_ID = 176;
    const LVCAN_AMOUNT_SPREAD_GRAVEL_ID = 193;
    const LVCAN_AMOUNT_SPREAD_SAND_ID = 178;
    const LVCAN_WIDTH_POURING_LEFT_ID = 183;
    const LVCAN_WIDTH_POURING_RIGHT_ID = 184;
    const LVCAN_SALT_SPREADER_WORK_HOURS_ID = 185;
    const LVCAN_DISTANCE_DURING_SALTING_ID = 186;
    const LVCAN_LOAD_WEIGHT_ID = 187;
    const LVC_RETARDER_LOAD_ID = 188;
    const LVC_CRUISE_TIME_ID = 189;
    const LVC_CNG_STATUS_ID = 190;
    const LVC_CNG_USED_ID = 191;
    const LVC_CNG_LEVEL_ID = 192;
    const GEOFENCE_ZONE_01_ID = 155;
    const GEOFENCE_ZONE_02_ID = 156;
    const GEOFENCE_ZONE_03_ID = 157;
    const GEOFENCE_ZONE_04_ID = 158;
    const GEOFENCE_ZONE_05_ID = 159;
    const AUTO_GEOFENCE_ID = 175;
    const IDLING_ID = 177;
    const JAMMING_DETECTION_ID = 249;
    const TRIP_ID = 250;
    const IMMOBILIZER_ID = 251;
    const AUTHORIZED_DRIVING_ID = 252;
    const GREEN_DRIVING_TYPE_ID = 253;
    const GREEN_DRIVING_VALUE_ID = 254;
    const OVER_SPEEDING_ID = 255;

    public static function getTotalOdometerId()
    {
        return self::ODOMETER_VALUE_VIRTUAL_ODOMETER_ID;
    }

    public static function getEngineHoursId()
    {
        return self::LVCAN_ENGINE_WORK_TIME_COUNTED_ID;
        // @todo: it's not working at all, verify with real devices
        // return self::LVCAN_ENGINE_WORK_TIME_ID;
    }

    public static function getTemperatureLevelId()
    {
        return self::LVCAN_ENGINE_TEMPERATURE_ID;
    }

    public static function getGSMSignalId()
    {
        return self::GSM_LEVEL_ID;
    }

    public static function getBatteryVoltageId()
    {
        return self::BATTERY_VOLTAGE_ID;
    }

    public static function getExternalVoltageId()
    {
        return self::EXTERNAL_POWER_VOLTAGE_ID;
    }

    public static function getIgnitionId()
    {
        return self::IGNITION_ID;
    }

    public static function getMovementId()
    {
        return self::MOVEMENT_SENSOR_ID;
    }

    public static function getIButtonId()
    {
        return self::IBUTTON_ID_ID;
    }
}
