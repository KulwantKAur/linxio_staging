<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLP1;

/**
 * From doc: 1=TSTH1-B, 2=TSDT1-B, 3=TSR1-B, 4=TPMS, 5=TZ T&H, 6=SOS Tag, 7=Driver ID, 8=Fuel Level
 */
class SensorTypes
{
    public const SENSOR_TYPE_ID_TSTH1_B = 1;
    public const SENSOR_TYPE_ID_TSDT1_B = 2;
    public const SENSOR_TYPE_ID_TSR1_B = 3;
    public const SENSOR_TYPE_ID_TPMS = 4;
    public const SENSOR_TYPE_ID_TZ_TH = 5;
    public const SENSOR_TYPE_ID_SOS_TAG = 6;
    public const SENSOR_TYPE_ID_DRIVER_ID = 7;
    public const SENSOR_TYPE_ID_FUEL_LEVEL = 8;
}