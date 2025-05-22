<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD2L;

use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\IOData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Position as PositionTLW1AndTLD1AE;

/**
 * @example 25251600506f24086447504960355700780e1014012c002453c000044100004001000000000081000000000022070103460800004040f7e2e7425574ffc1000001601264001702ebfd95000000000000
 */
class Position extends PositionTLW1AndTLD1AE
{
    public const PACKET_LENGTH = 118;

    public $acceleration;
    public $gyroscope;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->acceleration = $data['acceleration'] ?? null;
        $this->gyroscope = $data['gyroscope'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 18, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 70, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 70, 32));
        }

        return new self([
            'ignitionOnDuration' => hexdec(substr($textPayload, 0, 4)),
            'ignitionOffDuration' => hexdec(substr($textPayload, 4, 4)),
            'angleInterval' => hexdec(substr($textPayload, 8, 2)),
            'distanceInterval' => hexdec(substr($textPayload, 10, 4)),
            'overSpeedAlarmAndNetwork' => Data::formatOverSpeedAlarmAndNetwork(substr($textPayload, 14, 4)),
            'dataAndGNSS' => $dataAndGNSS,
            'gsensor' => Data::formatGsensor(substr($textPayload, 20, 2)),
            'other' => Data::formatOther(substr($textPayload, 22, 2)),
            'heartbeatDuration' => hexdec(substr($textPayload, 24, 2)),
            'relayStatus' => Data::formatRelayStatus(substr($textPayload, 26, 2)),
            'dragAlarm' => Data::formatDragAlarm(substr($textPayload, 28, 4)),
            'IOData' => IOData::createFromTextPayload(substr($textPayload, 32, 4)),
            'analogInput1' => Data::formatAnalogInput(substr($textPayload, 36, 4)),
            'analogInput2' => Data::formatAnalogInput(substr($textPayload, 40, 4)),
            'reserve' => Data::formatReserve(substr($textPayload, 46, 2)),
            'odometer' => hexdec(substr($textPayload, 48, 8)),
            'batteryVoltagePercentage' => Data::formatBatteryVoltagePercentage(substr($textPayload, 56, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 58, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'externalVoltage' => Data::formatExternalVoltage(substr($textPayload, 102, 4)),
            'acceleration' => Data::format3AxisesFromHex(substr($textPayload, 106, 12)),
            'gyroscope' => Data::format3AxisesFromHex(substr($textPayload, 118, 12)),
        ]);
    }
}
