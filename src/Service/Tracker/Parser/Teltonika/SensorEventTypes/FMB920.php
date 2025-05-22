<?php

namespace App\Service\Tracker\Parser\Teltonika\SensorEventTypes;

use App\Entity\DeviceModel;

/*
 * https://wiki.teltonika-gps.com/view/FMB920_Teltonika_Data_Sending_Parameters_ID
 */
class FMB920 extends FM3001
{
    public static $model = DeviceModel::TELTONIKA_FMB920;
}
