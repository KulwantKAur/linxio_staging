<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Pivotel;

use App\Entity\DeviceModel;
use App\Exceptions\UnsupportedException;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Parser\Pivotel\Model\GpsData;
use App\Service\Tracker\Parser\TrackerData;

/**
 * Class Data
 * @package App\Service\Tracker\Parser\Pivotel
 */
class Data extends TrackerData implements DeviceDataInterface
{
    public $imei;
    public $gpsData;
    public $movement = null;
    public $ignition = null;
    public $dateTime;
    public $batteryVoltagePercentage = null;

    public const CAUSE_STOP = 'STOP';

    public const BATTERY_STATUS_GOOD = 'Good';

    public function createFromPayload(\SimpleXMLElement $payload)
    {
        switch ($payload->deviceType) {
            case DeviceModel::PIVOTEL_SPOT_TRACE1:
                $this->createFromPayloadStr1($payload);
                break;
            case DeviceModel::PIVOTEL_9601:
                $this->createFromPayload9601($payload);
                break;
//            case DeviceModel::PIVOTEL_AMMT:
//                $this->createFromPayloadAmmt($payload);
//                break;
            default:
                throw new UnsupportedException('Unsupported device model name: ' . $payload->deviceType);
        }

        return $this;
    }

    private function createFromPayloadStr1(\SimpleXMLElement $payload)
    {
        $payloadData = $payload->message[0]->PSG_parameters;
        $this->setGpsData(new GpsData([
            'latitude' => (float)$payloadData->Latitude,
            'longitude' => (float)$payloadData->Longitude,
        ]));
        $this->setIgnition((string)$payloadData->Cause != self::CAUSE_STOP);
        $this->setMovement((string)$payloadData->Cause != self::CAUSE_STOP);
        $this->setImei((string)$payloadData->deviceID);
        $this->setDateTime(new \DateTime((string)$payload->msgTimestamp));
        $this->setBatteryVoltagePercentage((string)$payloadData->BattStatus == self::BATTERY_STATUS_GOOD ? 100 : 0);
    }

    private function createFromPayloadAmmt(\SimpleXMLElement $payload)
    {
        $payloadData = $payload->message[0]->AdditionalInfo;
        $this->setGpsData(new GpsData([
            'latitude' => (float)$payloadData->Latitude,
            'longitude' => (float)$payloadData->Longitude,
        ]));
        $this->setIgnition(true); //this device does not send info about ignition
        $this->setMovement(true); //this device does not send info about movement
        $this->setImei((string)$payloadData->deviceID);
        $this->setDateTime(new \DateTime((string)$payload->msgTimestamp));
        $this->setBatteryVoltagePercentage(100);
    }

    private function createFromPayload9601(\SimpleXMLElement $payload)
    {
        $payloadData = $payload->message[0]->AdditionalInfo;

        $this->setGpsData(new GpsData([
            'latitude' => (float)$payloadData->SBD_Lat,
            'longitude' => (float)$payloadData->SBD_Lon,
        ]));
        $this->setIgnition(true); //this device does not send info about ignition
        $this->setMovement(true); //this device does not send info about movement
        $this->setImei((string)$payloadData->deviceID);
        $this->setDateTime(new \DateTime((string)$payload->msgTimestamp));
        $this->setBatteryVoltagePercentage(100);
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
     * @return GpsDataInterface
     */
    public function getGpsData(): GpsDataInterface
    {
        return $this->gpsData;
    }

    public function setGpsData($data)
    {
        $this->gpsData = $data;
    }

    /**
     * @return null
     */
    public function getMovement()
    {
        return $this->movement;
    }

    /**
     * @param null $movement
     */
    public function setMovement($movement): void
    {
        $this->movement = $movement;
    }

    /**
     * @inheritDoc
     */
    public function getIgnition(?bool $isFixWithSpeed = null)
    {
        $ignitionBySpeed = (!is_null($isFixWithSpeed) && $isFixWithSpeed)
            ? (($this->getGpsData()->getSpeed() > 0) ? 1 : 0)
            : null;

        return !is_null($ignitionBySpeed) ? $ignitionBySpeed : $this->ignition;
    }

    /**
     * @param int|null $ignition
     */
    public function setIgnition($ignition): void
    {
        $this->ignition = $ignition;
    }

    /**
     * @return float|int|null
     */
    public function getBatteryVoltagePercentage()
    {
        return $this->batteryVoltagePercentage;
    }

    /**
     * @param float|int|null $batteryVoltagePercentage
     */
    public function setBatteryVoltagePercentage($batteryVoltagePercentage): void
    {
        $this->batteryVoltagePercentage = $batteryVoltagePercentage;
    }

    /**
     * @inheritDoc
     */
    public function getEngineOnTime()
    {
        // TODO: Implement getEngineOnTime() method.
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
    public function getSatellites(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getOdometer()
    {
        return null;
    }
}
