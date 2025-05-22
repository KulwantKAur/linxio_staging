<?php

namespace App\Service\Streamax\Model;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\PanicButtonInterface;
use App\Service\Tracker\Interfaces\SensorIOInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\TrackerData;
use Carbon\Carbon;

class StreamaxData extends TrackerData implements DeviceDataInterface, SensorIOInterface, PanicButtonInterface
{
    public const TYPE_GPS = 'GPS';
    public const TYPE_ALARM = 'ALARM';
    public const TYPE_TRAILER_BINDING = 'TRAILER_BINDING';
    public const TYPE_OBD = 'OBD';
    public const TYPE_PRIVACY = 'PRIVACY';
    public const TYPE_HF_RISK_SCORE = 'HF_RISK_SCORE';
    public const TYPE_ONLINE_STATE = 'ONLINE_STATE';
    public const TYPE_DOWNLOAD_STATE = 'DOWNLOAD_STATE';
    public const TYPE_IBUTTON = 'IBUTTON';
    public const SERVER_DEVICE_TIME_DIFF_SEC = 3600;

    private ?array $IOData = null;
    private bool $isPanicButton = false;
    private \DateTimeInterface $dateTime;
    private ?string $imei;
    private ?string $type;
    private ?GpsDataInterface $gpsData = null;
    private ?StreamaxAlarm $alarmData = null;
    private ?StreamaxOBD $OBDData = null;
    private ?StreamaxOnlineState $onlineStateData = null;
    private ?StreamaxIButton $IButtonData = null;
    private ?int $odometer = null;
    private ?int $movement = null;
    private ?int $ignition = null;
    private ?float $batteryVoltage = null;
    private ?float $deviceTemperature = null;
    private ?int $satellites = null;
    private ?int $engineOnTime = null;

    /**
     * @param Carbon $serverTime
     * @param Carbon $deviceTime
     * @return Carbon
     */
    private function getFixedTime(Carbon $serverTime, Carbon $deviceTime): Carbon
    {
        return $deviceTime;
        // @todo add fix if Streamax sends wrong date
        $deviceTimeReal = clone $deviceTime;

        return abs($serverTime->getTimestamp() - $deviceTime->getTimestamp()) > self::SERVER_DEVICE_TIME_DIFF_SEC
            ? (clone $serverTime)
                ->setMinutes($deviceTimeReal->minute)
                ->setSeconds($deviceTimeReal->second)
                ->setMillisecond(0)
                ->setMicroseconds(0)
            : $deviceTime;
    }

    /**
     * @return string|null
     */
    public function getImei(): ?string
    {
        return $this->imei;
    }

    /**
     * @param string|null $imei
     */
    public function setImei(?string $imei): void
    {
        $this->imei = $imei;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getDateTimeFromString(string $dateTime): Carbon
    {
        return Carbon::parse($dateTime);
    }

    public function setDateTime(\DateTimeInterface $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function createFromDataArray(array $data, string $type, string $serverTime, Device $device): self
    {
        $this->setType($type);
        $serverTime = $this->getDateTimeFromString($serverTime);

        match ($type) {
            self::TYPE_GPS => $this->createFromGPSSource($data, $serverTime, $device),
            self::TYPE_ALARM => $this->createFromAlarmSource($data, $serverTime, $device),
            self::TYPE_OBD => $this->createFromOBDSource($data, $serverTime, $device),
            self::TYPE_ONLINE_STATE => $this->createFromOnlineStateSource($data, $serverTime, $device),
            self::TYPE_IBUTTON => $this->createFromIButtonSource($data, $serverTime, $device),
            default => throw new \Exception('Unsupported type source: ' . $type)
        };

        return $this;
    }

    public function createFromGPSSource(array $data, Carbon $serverTime, Device $device)
    {
        if ($data) {
            $streamaxGPS = new StreamaxGPS($data);
            $deviceTime = $this->getDateTimeFromString($streamaxGPS->getTime());
            $this->setDateTime($this->getFixedTime($serverTime, $deviceTime));
            $this->setImei($streamaxGPS->getImei());
            $this->setGpsData($streamaxGPS);
            $this->setOdometer($streamaxGPS->getMileageMeters());
            $this->setMovement($streamaxGPS->getMovement());
            $this->setIgnition($streamaxGPS->getIgnition());
            $this->setDeviceTemperature($streamaxGPS->getTemperature());
            $this->setSatellites($streamaxGPS->getNumOfSatellites());
            $this->setBatteryVoltage($streamaxGPS->getVoltage());
        }
    }

    /**
     * @param array $data
     * @param Device $device
     * @throws \Exception
     */
    public function createFromAlarmSource(array $data, Carbon $serverTime, Device $device)
    {
        if ($data) {
            $streamaxAlarm = new StreamaxAlarm($data);
            $deviceTime = $this->getDateTimeFromString($streamaxAlarm->getTime());
            $this->setDateTime($this->getFixedTime($serverTime, $deviceTime));
            $this->setImei($streamaxAlarm->getImei());
            $this->setAlarmData($streamaxAlarm);
            $this->setGpsData($streamaxAlarm);

            if ($streamaxAlarm->isSOSType()) {
                $this->setIsPanicButton(true);
            }

            $this->setMovement($streamaxAlarm->getMovement());
            $this->setIgnition($streamaxAlarm->getIgnition());
        }
    }

    /**
     * @param array $data
     * @param Carbon $serverTime
     * @param Device $device
     */
    public function createFromOBDSource(array $data, Carbon $serverTime, Device $device)
    {
        if ($data) {
            $streamaxOBD = new StreamaxOBD($data);
            $deviceTime = $this->getDateTimeFromString($streamaxOBD->getTime());
            $this->setDateTime($this->getFixedTime($serverTime, $deviceTime));
            $this->setImei($streamaxOBD->getImei());
            $gpsData = new StreamaxGPS([]);
            $gpsData->setSpeed($streamaxOBD->getSpeed());
            $this->setGpsData($gpsData);
            $this->setOBDData($streamaxOBD);
        }
    }

    public function createFromOnlineStateSource(array $data, Carbon $serverTime, Device $device)
    {
        if ($data) {
            $onlineStateData = new StreamaxOnlineState($data);
            $deviceTime = $this->getDateTimeFromString($onlineStateData->getTime());
            $this->setDateTime($this->getFixedTime($serverTime, $deviceTime));
            $this->setImei($onlineStateData->getImei());
            $this->setOnlineStateData($onlineStateData);
        }
    }

    public function createFromIButtonSource(array $data, Carbon $serverTime, Device $device)
    {
        if ($data) {
            $IButtonData = new StreamaxIButton($data);
            $deviceTime = $this->getDateTimeFromString($IButtonData->getTime());
            $this->setDateTime($this->getFixedTime($serverTime, $deviceTime));
            $this->setImei($IButtonData->getImei());
            $this->setIButtonData($IButtonData);
        }
    }

    /**
     * @return GpsDataInterface|StreamaxGPS
     */
    public function getGpsData(): GpsDataInterface
    {
        return $this->gpsData ?: new StreamaxGPS([]);
    }

    /**
     * @param GpsDataInterface|StreamaxGPS|StreamaxAlarm|null $gpsData
     */
    public function setGpsData(?GpsDataInterface $gpsData): void
    {
        $this->gpsData = $gpsData;
    }

    public function getDriverIdTag(): ?string
    {
        return $this->getIButtonData()?->getIButtonId();
    }

    /**
     * @param int|null $engineOnTime
     */
    public function setEngineOnTime(?int $engineOnTime): void
    {
        $this->engineOnTime = $engineOnTime;
    }

    /**
     * @inheritDoc
     */
    public function getEngineOnTime(): ?int
    {
        return $this->engineOnTime;
    }

    /**
     * @param array|null $IOData
     */
    public function setIOData(?array $IOData): void
    {
        $this->IOData = $IOData;
    }

    /**
     * @return array|null
     */
    public function getIOData(): ?array
    {
        return $this->IOData;
    }

    /**
     * @return array|null
     */
    public function getSensorsIOData(): ?array
    {
        return $this->getIOData();
    }

    /**
     * @inheritDoc
     */
    public function getDTCVINData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getDriverBehaviorData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNetworkData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isPanicButton(): bool
    {
        return $this->isPanicButton;
    }

    /**
     * @param bool $isPanicButton
     */
    public function setIsPanicButton(bool $isPanicButton): void
    {
        $this->isPanicButton = $isPanicButton;
    }

    /**
     * @param int|null $satellites
     * @return void
     */
    public function setSatellites(?int $satellites): void
    {
        $this->satellites = $satellites;
    }

    /**
     * @inheritDoc
     */
    public function getSatellites(): ?int
    {
        return $this->satellites;
    }

    /**
     * @inheritDoc
     */
    public function isJammerAlarmStarted(): bool
    {
        return false;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return StreamaxAlarm|null
     */
    public function getAlarmData(): ?StreamaxAlarm
    {
        return $this->alarmData;
    }

    /**
     * @param StreamaxAlarm|null $alarmData
     */
    public function setAlarmData(?StreamaxAlarm $alarmData): void
    {
        $this->alarmData = $alarmData;
    }

    /**
     * @return string|null
     */
    public function getAlarmId(): ?string
    {
        return $this->getAlarmData()?->getAlarmId();
    }

    /**
     * @return int|null
     */
    public function getMovement(): ?int
    {
        return $this->movement;
    }

    /**
     * @param int|null $movement
     */
    public function setMovement(?int $movement): void
    {
        $this->movement = $movement;
    }

    /**
     * @inheritDoc
     */
    public function getIgnition(?bool $isFixWithSpeed = null)
    {
        return $this->ignition;
    }

    /**
     * @param int|null $ignition
     */
    public function setIgnition(?int $ignition): void
    {
        $this->ignition = $ignition;
    }

    public function getBatteryVoltageMilli(): ?int
    {
        return $this->getBatteryVoltage() ? $this->getBatteryVoltage() * 10 : null;
    }

    public function getBatteryVoltage(): ?float
    {
        return $this->batteryVoltage;
    }

    public function setBatteryVoltage(?float $batteryVoltage): void
    {
        $this->batteryVoltage = $batteryVoltage;
    }

    /**
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @param int|null $odometer
     */
    public function setOdometer(?int $odometer): void
    {
        $this->odometer = $odometer;
    }

    /**
     * @return int|null
     */
    public function getDeviceTemperature(): ?int
    {
        return $this->deviceTemperature;
    }

    /**
     * @return int|null
     */
    public function getDeviceTemperatureMilli(): ?int
    {
        return DataHelper::increaseValueToMilli($this->getDeviceTemperature());
    }

    /**
     * @param int|null $deviceTemperature
     */
    public function setDeviceTemperature(?int $deviceTemperature): void
    {
        $this->deviceTemperature = $deviceTemperature;
    }

    /**
     * @return StreamaxOBD|null
     */
    public function getOBDData(): ?StreamaxOBD
    {
        return $this->OBDData;
    }

    /**
     * @param StreamaxOBD|null $OBDData
     */
    public function setOBDData(?StreamaxOBD $OBDData): void
    {
        $this->OBDData = $OBDData;
    }

    /**
     * @return StreamaxOnlineState|null
     */
    public function getOnlineStateData(): ?StreamaxOnlineState
    {
        return $this->onlineStateData;
    }

    /**
     * @param StreamaxOnlineState|null $onlineStateData
     */
    public function setOnlineStateData(?StreamaxOnlineState $onlineStateData): void
    {
        $this->onlineStateData = $onlineStateData;
    }

    public function getIButtonData(): ?StreamaxIButton
    {
        return $this->IButtonData;
    }

    public function setIButtonData(?StreamaxIButton $IButtonData): void
    {
        $this->IButtonData = $IButtonData;
    }
}
