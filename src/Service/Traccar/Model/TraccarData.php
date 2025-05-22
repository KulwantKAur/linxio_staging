<?php

namespace App\Service\Traccar\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesConcox;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesDigitalMatter;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesMeitrack;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesQueclink;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesTeltonika;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributesUlbotech;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\PanicButtonInterface;
use App\Service\Tracker\Interfaces\SensorIOInterface;
use App\Service\Tracker\Parser\TrackerData;
use Carbon\Carbon;

class TraccarData extends TrackerData implements DeviceDataInterface, SensorIOInterface, PanicButtonInterface
{
    public const POSITION_SOURCE = 'position';
    public const EVENT_SOURCE = 'event';

    public const PROTOCOL_TELTONIKA = 'teltonika';
    public const PROTOCOL_ULBOTECH = 'ulbotech';
    public const PROTOCOL_CONCOX = 'gt06';
    public const PROTOCOL_MEITRACK = 'meitrack';
    public const PROTOCOL_QUECLINK = 'gl200';
    public const PROTOCOL_DIGITAL_MATTER = 'dmt';
    public const PROTOCOL_DIGITAL_MATTER_HTTP = 'dmthttp';
    public const PROTOCOL_EELINK = 'eelink';

    public const PROTOCOL_CONCOX_HOURS_OFFSET = 8;

    public $dateTime;
    /** @var string|null */
    public $imei;
    /** @var string|null */
    public $source;
    /** @var TraccarPosition|null */
    public $positionData;
    /** @var TraccarEvent|null */
    public $eventData;
    /** @var array|null */
    private $IOData = null;
    private bool $isPanicButton = false;
    private ?int $satellites;

    /**
     * @param \DateTimeInterface $fixTime
     * @param \DateTimeInterface $deviceTime
     * @param string|null $protocol
     * @param bool|null $isValid
     * @return \DateTimeInterface
     */
    private function updateDatetimeByProtocol(
        \DateTimeInterface $fixTime,
        \DateTimeInterface $deviceTime,
        ?string $protocol,
        ?bool $isValid
    ): \DateTimeInterface {
        switch ($protocol) {
            case self::PROTOCOL_CONCOX:
                $resultTime = $isValid && ($deviceTime > $fixTime) ? $deviceTime : $fixTime;

                return $resultTime->subHours(self::PROTOCOL_CONCOX_HOURS_OFFSET);
            default:
                return $fixTime;
        }
    }

    /**
     * @return mixed
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * @param mixed $imei
     */
    public function setImei($imei): void
    {
        $this->imei = $imei;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @param mixed $dateTime
     */
    public function setDateTime($dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $data
     * @param Device $device
     * @return self
     * @throws \Exception
     */
    public function createFromDataArray(array $data, Device $device): self
    {
        $source = $data['source'];
        $this->setSource($source);

        switch ($source) {
            case self::POSITION_SOURCE:
                $this->createFromPositionSource($data, $device);
                break;
            case self::EVENT_SOURCE:
                $this->createFromEventSource($data, $device);
                break;
            default:
                throw new \Exception('Unsupported data source: ' . $source);
        }

        return $this;
    }

    /**
     * @param array $data
     * @param Device $device
     * @throws \Exception
     */
    public function createFromPositionSource(array $data, Device $device)
    {
        if ($positionData = $data['position'] ?? null) {
            $traccarPosition = new TraccarPosition($positionData, $device);
            $this->setPositionData($traccarPosition);
            $traccarPositionAttributes = $traccarPosition->getAttributes();

            switch (true) {
                case ($traccarPositionAttributes instanceof TraccarPositionAttributesMeitrack):
                    if ($traccarPositionAttributes->getEvent()
                        && $traccarPositionAttributes->getEvent() == TraccarPositionAttributesMeitrack::EVENT_SOS_ID
                    ) {
                        $this->setIsPanicButton(true);
                    }

                    break;
                case ($traccarPositionAttributes instanceof TraccarPositionAttributesConcox):
                case ($traccarPositionAttributes instanceof TraccarPositionAttributesTeltonika):
                case ($traccarPositionAttributes instanceof TraccarPositionAttributesUlbotech):
                default:
                    break;
            }

            $fixDatetime = $this->updateDatetimeByProtocol(
                $traccarPosition->getFixTime(),
                $traccarPosition->getDeviceTime(),
                $traccarPosition->getProtocol(),
                $traccarPosition->isValid()
            );
            $this->setDateTime($fixDatetime);
            $this->setIOData($this->handleIOData($traccarPosition, $device));
        }
    }

    /**
     * @param array $data
     * @param Device $device
     * @throws \Exception
     */
    public function createFromEventSource(array $data, Device $device)
    {
        if ($positionData = $data['event'] ?? null) {
            $traccarEvent = new TraccarEvent($positionData, $device);
            $this->setEventData($traccarEvent);
            $traccarEventAttributes = $traccarEvent->getAttributes();

            if ($traccarEvent->getType() == TraccarEvent::ALARM_TYPE
                && $traccarEventAttributes->getAlarm()
                && $traccarEventAttributes->getAlarm() == TraccarEvent::ALARM_SOS
            ) {
                $this->setIsPanicButton(true);
            }

            $this->setDateTime($traccarEvent->getEventTime());
        }
    }

    public function getGpsData(): GpsDataInterface
    {
        // TODO: Implement getGpsData() method.
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     * @return TraccarData
     */
    public function setSource(?string $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param TraccarPosition|null $positionData
     * @return TraccarData
     */
    public function setPositionData(?TraccarPosition $positionData): TraccarData
    {
        $this->positionData = $positionData;

        return $this;
    }

    /**
     * @return TraccarPosition|null
     */
    public function getPositionData(): ?TraccarPosition
    {
        return $this->positionData;
    }

    /**
     * @return TraccarEvent|null
     */
    public function getEventData(): ?TraccarEvent
    {
        return $this->eventData;
    }

    /**
     * @param TraccarEvent|null $eventData
     * @return TraccarData
     */
    public function setEventData(?TraccarEvent $eventData): TraccarData
    {
        $this->eventData = $eventData;

        return $this;
    }

    /**
     * @param array $data
     * @param string $source
     * @return array
     */
    public static function setSourceToRawData(array $data, string $source)
    {
        $data['source'] = $source;

        return $data;
    }

    /**
     * @param array $data
     * @return int|null
     */
    public static function getDeviceIdFromRawData(array $data): ?int
    {
        return $data['device']['id'] ?? null;
    }

    /**
     * @param array $data
     * @return int|null
     */
    public static function getDeviceIdFromRawPositionData(array $data): ?int
    {
        return $data['deviceId'] ?? null;
    }

    /**
     * @param array $data
     * @return int|null
     */
    public static function getDeviceIdFromRawEventData(array $data): ?int
    {
        return self::getDeviceIdFromRawPositionData($data);
    }

    /**
     * @param array $data
     * @return array|null
     */
    public static function getPositionDataFromRawData(array $data): ?array
    {
        return $data['position'] ?? null;
    }

    /**
     * @param array $data
     * @return array|null
     */
    public static function getEventDataFromRawData(array $data): ?array
    {
        return $data['event'] ?? null;
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function isPositionRawDataValid(array $data): bool
    {
        return $data['id'] != 0;
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function isEventRawDataValid(array $data): bool
    {
        return self::isPositionRawDataValid($data);
    }

    /**
     * @param TraccarPosition $traccarPosition
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @return TrackerHistory
     */
    public static function fillTrackerHistoryByPositionAttributes(
        TraccarPosition $traccarPosition,
        TrackerHistory $trackerHistory,
        Device $device
    ): TrackerHistory {
        $traccarPositionAttributes = $traccarPosition->getAttributes();
        $trackerHistory->setMovement($traccarPositionAttributes->getMotion());
        $ignition = ($device->isFixWithSpeed() && ($traccarPosition->getSpeed() > 0))
            ? 1
            : $traccarPositionAttributes->getIgnition();
        $trackerHistory->setIgnition($ignition);
        $trackerHistory->setOdometer(
            $traccarPositionAttributes->getOdometer(),
            $device->getLastTrackerRecord()
        );
        $trackerHistory->setSatellites($traccarPositionAttributes->getSat());
        $trackerHistory->setTemperatureLevel($traccarPositionAttributes->getTemperatureMilli());
        $trackerHistory->setExternalVoltage($traccarPositionAttributes->getPowerMilli());

        switch (true) {
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesTeltonika):
                $trackerHistory->setBatteryVoltage($traccarPositionAttributes->getBatteryMilli());
                $trackerHistory->setEngineOnTime($traccarPositionAttributes->getHours());
                $trackerHistory->setOdometer(
                    $traccarPositionAttributes->getTotalDistanceRound(),
                    $device->getLastTrackerRecord()
                );
                // @todo get from IO events
                // $this->setTemperatureLevel(null);
                break;
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesConcox):
                $trackerHistory->setEngineOnTime($traccarPositionAttributes->getHours());
                $trackerHistory->setBatteryVoltagePercentage($traccarPositionAttributes->getBatteryLevel());

                if (!$device->getIccid() && $traccarPositionAttributes->getIccid()) {
                    $device->setIccid($traccarPositionAttributes->getIccid());
                }
                break;
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesMeitrack):
                $trackerHistory->setEngineOnTime($traccarPositionAttributes->getRuntime());
                $trackerHistory->setBatteryVoltage($traccarPositionAttributes->getBatteryMilli());
                break;
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesDigitalMatter):
                $trackerHistory->setBatteryVoltage($traccarPositionAttributes->getBatteryMilli());
                $trackerHistory->setOdometer(
                    $traccarPositionAttributes->getTotalDistanceRound(),
                    $device->getLastTrackerRecord()
                );
                break;
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesUlbotech):
            case ($traccarPositionAttributes instanceof TraccarPositionAttributesQueclink):
            default:
                break;
        }

        return $trackerHistory;
    }

    /**
     * @inheritDoc
     */
    public function getIgnition(?bool $isFixWithSpeed = null)
    {
        // TODO: Implement getIgnition() method.
    }

    /**
     * @inheritDoc
     */
    public function getEngineOnTime()
    {
        // TODO: Implement getEngineOnTime() method.
    }

    /**
     * @param TraccarPosition $traccarPosition
     * @param Device|null $device
     * @return array|null
     * @throws \Exception
     */
    public function handleIOData(TraccarPosition $traccarPosition, ?Device $device): ?array
    {
        $protocol = $traccarPosition->getProtocol();

        switch ($protocol) {
            case TraccarData::PROTOCOL_TELTONIKA:
                $positionAttributes = $traccarPosition->getAttributes();
                $IOData = $positionAttributes->getIOData();
                break;
            case TraccarData::PROTOCOL_ULBOTECH:
            default:
                $IOData = null;
                break;
        }

        return $IOData;
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
     * @inheritDoc
     */
    public function getSatellites(): ?int
    {
        return $this->getPositionData() && $this->getPositionData()->getAttributes()
            ? $this->getPositionData()->getAttributes()->getSat()
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getOdometer()
    {
        return $this->getPositionData() && $this->getPositionData()->getAttributes()
            ? $this->getPositionData()->getAttributes()->getOdometer()
            : null;
    }

    public static function isModelNameConcoxCRX3(string $modelName): bool
    {
        return in_array($modelName, [
            DeviceModel::TRACCAR_CONCOX_CRX3,
            DeviceModel::TRACCAR_CONCOX_LINXIO_TR500,
            DeviceModel::TRACCAR_CONCOX_LINXIO_VX60,
        ]);
    }
}
