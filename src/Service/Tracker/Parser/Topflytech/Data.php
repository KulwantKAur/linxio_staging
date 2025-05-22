<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech;

use App\Entity\DeviceModel;
use App\Service\Tracker\Interfaces\PanicButtonInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAccidentViaAcc;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Service\Tracker\Parser\Topflytech\Model\BaseIOData;
use App\Service\Tracker\Parser\Topflytech\Model\BaseNetwork;
use App\Service\Tracker\Parser\Topflytech\Model\BaseOutputData;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensor;
use App\Service\Tracker\Parser\Topflytech\Model\BaseOneWire;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\BLE AS BLETLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\DTCAndVIN;
use App\Service\Tracker\Parser\Topflytech\Model\TLD2L\Position as PositionTLD2L;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE AS BLETLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\BLE AS BLETLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\BLE AS BLETLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\AccidentViaAcc;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\AccidentViaAcc as AccidentViaAccTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\AccidentViaAcc as AccidentViaAccTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Alarm;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\Alarm as AlarmTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\Alarm as AlarmTLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\Alarm as AlarmTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLD2L\Alarm as AlarmTLD2L;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\DriverBehaviorAM;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\DriverBehaviorAM AS DriverBehaviorAMTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\DriverBehaviorAM AS DriverBehaviorAMTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\DriverBehaviorGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\DriverBehaviorGNSS AS DriverBehaviorGNSSTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\DriverBehaviorGNSS AS DriverBehaviorGNSSTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Position;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\Position as PositionTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\Position as PositionTLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\Position as PositionTLW212BL;
use App\Service\Tracker\Parser\TrackerData;

/**
 * Class Data
 * @package App\Service\Tracker\Parser\Topflytech
 */
class Data extends TrackerData implements DeviceDataInterface, PanicButtonInterface
{
    public const LOGIN_MESSAGE_TYPE = '01';
    public const POSITION_MESSAGE_TYPE = '02';
    public const HEARTBEAT_MESSAGE_TYPE = '03';
    public const ALARM_MESSAGE_TYPE = '04';
    public const DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE = '05';
    public const DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE = '06';
    public const ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE = '07';
    public const RS232_MESSAGE_TYPE = '09';
    public const DTC_VIN_MESSAGE_TYPE = '09';
    public const BLE_MESSAGE_TYPE = '10';
    public const NETWORK_INFORMATION_MESSAGE_TYPE = '11';
    public const NETWORK_INFORMATION_2727_MESSAGE_TYPE = '05';
    public const SETTING_MESSAGE_TYPE = '81';
    public const POSITION_MESSAGE_TLW2_TYPE = '13';
    public const ALARM_MESSAGE_TLW2_TYPE = '14';
    public const BLE_MESSAGE_WITH_POSITION_TYPE = '12';
    public const POSITION_MESSAGE_TLD2_L_TYPE = '16';
    public const ALARM_MESSAGE_TLD2_L_TYPE = '18';
    public const ONEWIRE_MESSAGE_TYPE = '23';

    public const DATA_START_PACKET_POSITION = 30;
    public const DATETIME_FORMAT = 'ymdHis';
    public const ODOMETER_LIMIT_MAX = 2000000000; // 4294967295 by doc
    public const ODOMETER_LIMIT_MIN = 10000000; // 2651166 by real data
    public const ODOMETER_VALUE_WITH_ERROR = 4294967295;

    public $header;
    public $imei;
    public $positionData;
    public $gpsData;
    public $locationData;
    public $movement = null;
    public $ignition = null;
    public ?float $speed = null;
    public $dateTime;
    public $batteryVoltage = null;
    public $batteryVoltagePercentage = null;
    public $externalVoltage = null;
    public $odometer = null;
    public $driverBehaviorData;
    public $alarmData;
    public $accidentData;
    public $deviceTemperature = null;
    public $solarChargingStatus = null;
    public $engineOnTime = null; // seconds
    public ?BaseBLE $BLEData = null;
    public $BLEDataArray = null;
    public ?string $driverIdTag = null;
    public ?string $driverFOBId = null;
    public $BLEDriverSensorData = null;
    public $OBDData = null;
    public $BLETempAndHumidityData = null;
    public ?array $BLESOSData = null;
    public $isSOSAlarm = false;
    public bool $isJammerAlarm = false;
    public ?BaseIOData $IOData = null;
    public ?BaseOutputData $outputData = null;
    public ?DTCAndVIN $DTCVINData = null;
    public ?BaseNetwork $networkData = null;
    public ?BaseOneWire $oneWireData = null;
    public ?int $satellites = null;

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

    public function createFromTextPayload(string $payload, DeviceModel $deviceModel): self
    {
        switch ($deviceModel->getName()) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLD2_L:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                $this->createFromTextPayloadTLW1AndTLD1AE($payload);
                break;
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                $this->createFromTextPayloadTLW2($payload);
                break;
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
                $this->createFromTextPayloadTLD1DADE($payload);
                break;
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                $this->createFromTextPayloadTLP1($payload);
                break;
            default:
                throw new \Exception('Unsupported device model name: ' . $deviceModel->getName());
        }

        return $this;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public function createFromTextPayloadTLW1AndTLD1AE(string $payload): self
    {
        $basePacket = BasePacket::createFromTextPayload($payload);
        $restPayload = substr($payload, self::DATA_START_PACKET_POSITION);

        switch ($basePacket->getMessageType()) {
            case self::POSITION_MESSAGE_TYPE:
                $this->setPositionData(Position::createFromTextPayload($restPayload));
                $this->fromPositionData($this->getPositionData());
                break;
            case self::ALARM_MESSAGE_TYPE:
                $this->setAlarmData(Alarm::createFromTextPayload($restPayload));
                $this->fromAlarmData($this->getAlarmData());
                break;
            case self::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorGNSS::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorGNSSData($this->getDriverBehaviorData());
                break;
            case self::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorAM::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorAccData($this->getDriverBehaviorData());
                break;
            case self::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                $this->setAccidentData(AccidentViaAcc::createFromTextPayload($restPayload));

                if (AccidentViaAcc::hasPosition($restPayload)) {
                    $this->setPositionData(Position::createFromTextPayload(
                        AccidentViaAcc::getPositionPayload($restPayload)
                    ));
                    $this->fromPositionData($this->getPositionData());
                } else {
                    $this->fromAccidentData($this->getAccidentData());
                }
                break;
            case self::BLE_MESSAGE_TYPE:
                $this->setBLEData(BLETLW1AndTLD1AE::createFromTextPayload($restPayload));
                $this->fromBLEData($this->getBLEData());
                break;
            case self::NETWORK_INFORMATION_MESSAGE_TYPE:
                $this->setNetworkData(BaseNetwork::createFromTextPayload($restPayload));
                $this->fromNetworkData($this->getNetworkData());
                break;
            case self::BLE_MESSAGE_WITH_POSITION_TYPE:
                // @todo
                break;
            case self::POSITION_MESSAGE_TLD2_L_TYPE:
                $this->setPositionData(PositionTLD2L::createFromTextPayload($restPayload));
                $this->fromPositionData($this->getPositionData());
                break;
            case self::ALARM_MESSAGE_TLD2_L_TYPE:
                $this->setAlarmData(AlarmTLD2L::createFromTextPayload($restPayload));
                $this->fromAlarmData($this->getAlarmData());
                break;
            default:
                break;
        }

        return $this;
    }

    public function createFromTextPayloadTLW2(string $payload): self
    {
        $basePacket = BasePacket::createFromTextPayload($payload);
        $restPayload = substr($payload, self::DATA_START_PACKET_POSITION);

        switch ($basePacket->getMessageType()) {
            case self::POSITION_MESSAGE_TLW2_TYPE:
                $this->setPositionData(PositionTLW212BL::createFromTextPayload($restPayload));
                $this->fromTLW212BLPositionData($this->getPositionData());
                break;
            case self::ALARM_MESSAGE_TLW2_TYPE:
                $this->setAlarmData(AlarmTLW212BL::createFromTextPayload($restPayload));
                $this->fromTLW212BLAlarmData($this->getAlarmData());
                break;
            case self::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorGNSSTLW212BL::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorGNSSData($this->getDriverBehaviorData());
                break;
            case self::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorAMTLW212BL::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorAccData($this->getDriverBehaviorData());
                break;
            case self::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                $this->setAccidentData(AccidentViaAccTLW212BL::createFromTextPayload($restPayload));

                if (AccidentViaAccTLW212BL::hasPosition($restPayload)) {
                    $this->setPositionData(PositionTLW212BL::createFromTextPayload(
                        AccidentViaAccTLW212BL::getPositionPayload($restPayload)
                    ));
                    $this->fromTLW212BLPositionData($this->getPositionData());
                } else {
                    $this->fromAccidentData($this->getAccidentData());
                }
                break;
            case self::BLE_MESSAGE_TYPE:
                $this->setBLEData(BLETLW212BL::createFromTextPayload($restPayload));
                $this->fromBLEData($this->getBLEData());
                break;
            case self::NETWORK_INFORMATION_MESSAGE_TYPE:
                $this->setNetworkData(BaseNetwork::createFromTextPayload($restPayload));
                $this->fromNetworkData($this->getNetworkData());
                break;
            case self::ONEWIRE_MESSAGE_TYPE:
                $this->setOneWireData(BaseOneWire::createFromTextPayload($restPayload));
                $this->fromOneWireData($this->getOneWireData());
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public function createFromTextPayloadTLD1DADE(string $payload): self
    {
        $basePacket = BasePacket::createFromTextPayload($payload);
        $restPayload = substr($payload, self::DATA_START_PACKET_POSITION);

        switch ($basePacket->getMessageType()) {
            case self::POSITION_MESSAGE_TYPE:
                $this->setPositionData(PositionTLD1DADE::createFromTextPayload($restPayload));
                $this->fromTLD1DADEPositionData($this->getPositionData());
                break;
            case self::ALARM_MESSAGE_TYPE:
                $this->setAlarmData(AlarmTLD1DADE::createFromTextPayload($restPayload));
                $this->fromTLD1DADEAlarmData($this->getAlarmData());
                break;
            case self::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorGNSSTLD1DADE::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorGNSSData($this->getDriverBehaviorData());
                break;
            case self::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                $this->setDriverBehaviorData(DriverBehaviorAMTLD1DADE::createFromTextPayload($restPayload));
                $this->fromDriverBehaviorAccData($this->getDriverBehaviorData());
                break;
            case self::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                $this->setAccidentData(AccidentViaAccTLD1DADE::createFromTextPayload($restPayload));

                if (AccidentViaAccTLD1DADE::hasPosition($restPayload)) {
                    $this->setPositionData(PositionTLD1DADE::createFromTextPayload(
                        AccidentViaAccTLD1DADE::getPositionPayload($restPayload)
                    ));
                    $this->fromTLD1DADEPositionData($this->getPositionData());
                } else {
                    $this->fromAccidentData($this->getAccidentData());
                }
                break;
            case self::BLE_MESSAGE_TYPE:
                $this->setBLEData(BLETLD1DADE::createFromTextPayload($restPayload));
                $this->fromBLEData($this->getBLEData());
                break;
            case self::DTC_VIN_MESSAGE_TYPE:
                $this->setDTCVINData(DTCAndVIN::createFromTextPayload($restPayload));
                $this->fromDTCVINData($this->getDTCVINData());
                break;
            case self::NETWORK_INFORMATION_MESSAGE_TYPE:
                $this->setNetworkData(BaseNetwork::createFromTextPayload($restPayload));
                $this->fromNetworkData($this->getNetworkData());
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public function createFromTextPayloadTLP1(string $payload): self
    {
        $basePacket = BasePacket::createFromTextPayload($payload);
        $restPayload = substr($payload, self::DATA_START_PACKET_POSITION);

        switch ($basePacket->getMessageType()) {
            case self::POSITION_MESSAGE_TYPE:
                $this->setPositionData(PositionTLP1::createFromTextPayload($restPayload));
                $this->fromTLP1PositionData($this->getPositionData());
                break;
            case self::ALARM_MESSAGE_TYPE:
                $this->setAlarmData(AlarmTLP1::createFromTextPayload($restPayload));
                $this->fromTLP1AlarmData($this->getAlarmData());
                break;
            case self::BLE_MESSAGE_TYPE:
                $this->setBLEData(BLETLP1::createFromTextPayload($restPayload));
                $this->fromBLEData($this->getBLEData());
                break;
            case self::NETWORK_INFORMATION_2727_MESSAGE_TYPE:
                $this->setNetworkData(BaseNetwork::createFromTextPayload($restPayload));
                $this->fromNetworkData($this->getNetworkData());
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @return GpsDataInterface
     */
    public function getGpsData(): GpsDataInterface
    {
        return $this->gpsData ?: new GpsData([]);
        // @todo implement LBS part for future
        // return $this->gpsData ?: ($this->getLocation() ?: new GpsData([]));
    }

    /**
     * @param GpsDataInterface|null $gpsData
     */
    public function setGpsData(?GpsDataInterface $gpsData): void
    {
        $this->gpsData = $gpsData;
    }

    /**
     * @return BaseAlarm|null
     */
    public function getAlarmData(): ?BaseAlarm
    {
        return $this->alarmData;
    }

    /**
     * @return array|null
     */
    public function getAlarmTypeData(): ?array
    {
        return $this->getAlarmData() ? ['type' => $this->getAlarmData()->getType()] : null;
    }

    /**
     * @param BaseAlarm|null $alarmData
     */
    public function setAlarmData(?BaseAlarm $alarmData): void
    {
        $this->alarmData = $alarmData;
    }

    /**
     * @return DriverBehaviorBase|null
     */
    public function getDriverBehaviorData(): ?DriverBehaviorBase
    {
        return $this->driverBehaviorData;
    }

    /**
     * @param DriverBehaviorBase $driverBehaviorData
     */
    public function setDriverBehaviorData(DriverBehaviorBase $driverBehaviorData): void
    {
        $this->driverBehaviorData = $driverBehaviorData;
    }

    /**
     * @return BasePosition|null
     */
    public function getPositionData()
    {
        return $this->positionData;
    }

    /**
     * @param mixed|BasePosition $positionData
     */
    public function setPositionData($positionData): void
    {
        $this->positionData = $positionData;
    }

    /**
     * @return mixed
     */
    public function getLocationData()
    {
        return $this->locationData;
    }

    /**
     * @param mixed $locationData
     */
    public function setLocationData($locationData): void
    {
        $this->locationData = $locationData;
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
     * @return mixed
     */
    public function getBatteryVoltage()
    {
        return $this->batteryVoltage;
    }

    /**
     * @return mixed
     */
    public function getBatteryVoltageMilli()
    {
        return DataHelper::increaseValueToMilli($this->getBatteryVoltage());
    }

    /**
     * @param mixed $batteryVoltage
     */
    public function setBatteryVoltage($batteryVoltage): void
    {
        $this->batteryVoltage = $batteryVoltage;
    }

    /**
     * @return mixed|null
     */
    public function getOdometer()
    {
        return $this->odometer;
    }

    /**
     * @param mixed|null $odometer
     */
    public function setOdometer($odometer): void
    {
        $this->odometer = $odometer;
    }

    /**
     * @return null
     */
    public function getExternalVoltage()
    {
        return $this->externalVoltage;
    }

    /**
     * @return null
     */
    public function getExternalVoltageMilli()
    {
        return DataHelper::increaseValueToMilli($this->getExternalVoltage());
    }

    /**
     * @param null $externalVoltage
     */
    public function setExternalVoltage($externalVoltage): void
    {
        $this->externalVoltage = $externalVoltage;
    }

    /**
     * @return BaseAccidentViaAcc|null
     */
    public function getAccidentData(): ?BaseAccidentViaAcc
    {
        return $this->accidentData;
    }

    /**
     * @param BaseAccidentViaAcc|null $accidentData
     */
    public function setAccidentData(?BaseAccidentViaAcc $accidentData): void
    {
        $this->accidentData = $accidentData;
    }

    /**
     * @param Alarm $alarmData
     */
    public function fromAlarmData(Alarm $alarmData): void
    {
        $this->setGpsData($alarmData->getGpsData());
        $this->setLocationData($alarmData->getLocationData());
        $this->setDateTime($alarmData->getDateTime());
        $this->setMovement($alarmData->getMovement());
        $this->setIOData($alarmData->getIOData());
        $this->setIgnition($alarmData->getIOData()
            ? $alarmData->getIOData()->getIgnitionInput()
            : $alarmData->getIgnition());
        $this->setBatteryVoltage(null);
        $this->setBatteryVoltagePercentage($alarmData->getBatteryVoltagePercentage());
        $this->setExternalVoltage($alarmData->getExternalVoltage());
        $this->setOdometer($alarmData->getOdometer());
        $this->setIsSOSAlarm($alarmData->isPanicButton());
        $this->setIsJammerAlarm($alarmData->isJammerAlarm());

        if ($dataAndGNSS = $alarmData->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param AlarmTLW212BL $alarmData
     */
    public function fromTLW212BLAlarmData(AlarmTLW212BL $alarmData): void
    {
        $this->setGpsData($alarmData->getGpsData());
        $this->setLocationData($alarmData->getLocationData());
        $this->setDateTime($alarmData->getDateTime());
        $this->setMovement($alarmData->getMovement());
        $this->setIOData($alarmData->getIOData());
        $this->setOutputData($alarmData->getDigitalOutput());
        $this->setIgnition($alarmData->getIOData()
            ? $alarmData->getIOData()->getIgnitionInput()
            : $alarmData->getIgnition());
        $this->setBatteryVoltage(null);
        $this->setBatteryVoltagePercentage($alarmData->getBatteryVoltagePercentage());
        $this->setExternalVoltage($alarmData->getExternalVoltage());
        $this->setOdometer($alarmData->getOdometer());
        $this->setIsSOSAlarm($alarmData->isPanicButton());
        $this->setDeviceTemperature($alarmData->getDeviceTemperature());
        $this->setBatteryVoltage($alarmData->getInternalBatteryVoltage());

        if ($dataAndGNSS = $alarmData->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param AlarmTLD1DADE $alarmData
     */
    public function fromTLD1DADEAlarmData(AlarmTLD1DADE $alarmData): void
    {
        $this->setSpeed($alarmData->getSpeed());
        $this->setGpsData($alarmData->getGpsData());
        $this->setLocationData($alarmData->getLocationData());
        $this->setDateTime($alarmData->getDateTime());
        $this->setMovement($alarmData->getMovement());
        $this->setIOData($alarmData->getIOData());
        $this->setIgnition($alarmData->getIOData()
            ? $alarmData->getIOData()->getIgnitionInput()
            : $alarmData->getIgnition());
        $this->setBatteryVoltage(null);
        $this->setBatteryVoltagePercentage($alarmData->getBatteryVoltagePercentage());
        $this->setExternalVoltage($alarmData->getExternalVoltage());
        $this->setOdometer($alarmData->getOdometer());
        $this->setOBDData($alarmData->getOBDData());
        $this->setIsSOSAlarm($alarmData->isPanicButton());
        $this->setIsJammerAlarm($alarmData->isJammerAlarm());

        if ($dataAndGNSS = $alarmData->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param AlarmTLP1 $alarmData
     */
    public function fromTLP1AlarmData(AlarmTLP1 $alarmData): void
    {
        $this->setGpsData($alarmData->getGpsData());
        $this->setLocationData($alarmData->getLocationData());
        $this->setDateTime($alarmData->getDateTime());
        $this->setMovement($alarmData->getMovement());
        $this->setIgnition($alarmData->getIgnition());
        $this->setBatteryVoltage($alarmData->getInternalBatteryVoltage());
        $this->setBatteryVoltagePercentage($alarmData->getBatteryVoltagePercentage());
        $this->setSolarChargingStatus($alarmData->getSolarChargingStatus());
        $this->setExternalVoltage(null);
        $this->setOdometer($alarmData->getOdometer());
        $this->setDeviceTemperature($alarmData->getDeviceTemperature());
        $this->setIsSOSAlarm($alarmData->isPanicButton());

        if ($dataAndGNSS = $alarmData->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param Position $position
     */
    public function fromPositionData(Position $position): void
    {
        $this->setDateTime($position->getDateTime());
        $this->setGpsData($position->getGpsData());
        $this->setLocationData($position->getLocationData());
        $this->setMovement($position->getMovement());
        $this->setIOData($position->getIOData());
        $this->setIgnition($position->getIOData()
            ? $position->getIOData()->getIgnitionInput()
            : $position->getIgnition());
        $this->setBatteryVoltagePercentage($position->getBatteryVoltagePercentage());
        $this->setExternalVoltage($position->getExternalVoltage());
        $this->setOdometer($position->getOdometer());

        if ($dataAndGNSS = $position->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param PositionTLW212BL $position
     */
    public function fromTLW212BLPositionData(PositionTLW212BL $position): void
    {
        $this->setDateTime($position->getDateTime());
        $this->setGpsData($position->getGpsData());
        $this->setLocationData($position->getLocationData());
        $this->setMovement($position->getMovement());
        $this->setIOData($position->getIOData());
        $this->setOutputData($position->getDigitalOutput());
        $this->setIgnition($position->getIOData()
            ? $position->getIOData()->getIgnitionInput()
            : $position->getIgnition());
        $this->setBatteryVoltagePercentage($position->getBatteryVoltagePercentage());
        $this->setExternalVoltage($position->getExternalVoltage());
        $this->setOdometer($position->getOdometer());
        $this->setDeviceTemperature($position->getDeviceTemperature());
        $this->setBatteryVoltage($position->getInternalBatteryVoltage());

        if ($dataAndGNSS = $position->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param PositionTLD1DADE $position
     */
    public function fromTLD1DADEPositionData(PositionTLD1DADE $position): void
    {
        $this->setSpeed($position->getSpeed());
        $this->setDateTime($position->getDateTime());
        $this->setGpsData($position->getGpsData());
        $this->setLocationData($position->getLocationData());
        $this->setMovement($position->getMovement());
        $this->setIOData($position->getIOData());
        $this->setIgnition($position->getIOData()
            ? $position->getIOData()->getIgnitionInput()
            : $position->getIgnition());
        $this->setBatteryVoltagePercentage($position->getBatteryVoltagePercentage());
        $this->setExternalVoltage($position->getExternalVoltage());
        $this->setOdometer($position->getOdometer());
        $this->setOBDData($position->getOBDData());

        if ($dataAndGNSS = $position->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param PositionTLP1 $position
     */
    public function fromTLP1PositionData(PositionTLP1 $position): void
    {
        $this->setDateTime($position->getDateTime());
        $this->setGpsData($position->getGpsData());
        $this->setLocationData($position->getLocationData());
        $this->setMovement($position->getMovement());
        $this->setIgnition($position->getStatus() ? $position->getStatus()->getIgnition() : $position->getIgnition());
        $this->setBatteryVoltage($position->getInternalBatteryVoltage());
        $this->setBatteryVoltagePercentage($position->getBatteryVoltagePercentage());
        $this->setSolarChargingStatus($position->getSolarChargingStatus());
        $this->setExternalVoltage(null);
        $this->setOdometer($position->getOdometer());
        $this->setDeviceTemperature($position->getDeviceTemperature());

        if ($dataAndGNSS = $position->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param DriverBehaviorBase $driverBehaviorGNSS
     */
    public function fromDriverBehaviorGNSSData(DriverBehaviorBase $driverBehaviorGNSS): void
    {
        $this->setGpsData($driverBehaviorGNSS->getGpsData());
        $this->setDateTime($driverBehaviorGNSS->getDateTime());
        $this->setMovement($driverBehaviorGNSS->getMovement());
        $this->setIgnition($driverBehaviorGNSS->getIgnition());

        if ($dataAndGNSS = $driverBehaviorGNSS->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param DriverBehaviorBase $driverBehaviorAcc
     */
    public function fromDriverBehaviorAccData(DriverBehaviorBase $driverBehaviorAcc): void
    {
        $this->setGpsData($driverBehaviorAcc->getGpsData());
        $this->setLocationData($driverBehaviorAcc->getLocationData());
        $this->setDateTime($driverBehaviorAcc->getDateTime());
        $this->setMovement($driverBehaviorAcc->getMovement());
        $this->setIgnition($driverBehaviorAcc->getIgnition());

        if ($dataAndGNSS = $driverBehaviorAcc->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param BaseAccidentViaAcc $accidentViaAcc
     */
    public function fromAccidentData(BaseAccidentViaAcc $accidentViaAcc): void
    {
        $this->setGpsData($accidentViaAcc->getGpsData());
        $this->setLocationData($accidentViaAcc->getLocationData());
        $this->setDateTime($accidentViaAcc->getDateTime());
        $this->setMovement($accidentViaAcc->getMovement());
        $this->setIgnition($accidentViaAcc->getIgnition());

        if ($dataAndGNSS = $accidentViaAcc->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    public function fromBLEData(BaseBLE $baseBLEData): void
    {
        $this->setGpsData($baseBLEData->getGpsData());
        $this->setLocationData($baseBLEData->getLocationData());
        $this->setDateTime($baseBLEData->getDateTime());
        $this->setMovement($baseBLEData->getMovement());
        $this->setIgnition($baseBLEData->getIgnition());
        $this->setDriverIdTag($baseBLEData->getDriverIdTag());
        $this->setBLETempAndHumidityData($baseBLEData->getBLETempAndHumidityData());
        $this->setBLEDriverSensorData($baseBLEData->getDriverSensorData());
        $this->setBLESOSData($baseBLEData->getBLESOSData());
        $this->setIsSOSAlarm($baseBLEData->isBLESOSAlarm());

        if ($dataAndGNSS = $baseBLEData->getDataAndGNSS()) {
            $this->setSatellites($dataAndGNSS->getSatellites());
        }
    }

    /**
     * @param BaseNetwork $baseNetwork
     */
    public function fromNetworkData(BaseNetwork $baseNetwork): void
    {
        $this->setDateTime($baseNetwork->getDateTime());
    }

    /**
     * @param DTCAndVIN $baseBLEData
     */
    public function fromDTCVINData(DTCAndVIN $baseBLEData): void
    {
        $this->setDateTime($baseBLEData->getDateTime());
    }

    public function fromOneWireData(BaseOneWire $baseOneWire): void
    {
        $this->setDateTime($baseOneWire->getDateTime());
        $this->setIgnition($baseOneWire->getIgnition());
        $this->setDriverFOBId($baseOneWire->getDriverIdTag());
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperature()
    {
        return $this->deviceTemperature;
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperatureMilli()
    {
        return DataHelper::increaseValueToMilli($this->getDeviceTemperature());
    }

    /**
     * @param float|null $deviceTemperature
     */
    public function setDeviceTemperature($deviceTemperature): void
    {
        $this->deviceTemperature = $deviceTemperature;
    }

    /**
     * @return bool|null
     */
    public function getSolarChargingStatus(): ?bool
    {
        return $this->solarChargingStatus;
    }

    /**
     * @param bool|null $solarChargingStatus
     */
    public function setSolarChargingStatus(?bool $solarChargingStatus): void
    {
        $this->solarChargingStatus = $solarChargingStatus;
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
     * @return int|null
     */
    public function getEngineOnTime()
    {
        return $this->engineOnTime;
    }

    /**
     * @param int|null $engineOnTime
     */
    public function setEngineOnTime($engineOnTime): void
    {
        $this->engineOnTime = $engineOnTime;
    }

    /**
     * @return BaseBLE|null
     */
    public function getBLEData()
    {
        return $this->BLEData;
    }

    /**
     * @param BaseBLE|null $BLEData
     */
    public function setBLEData($BLEData): void
    {
        $this->BLEData = $BLEData;
    }

    public function getDriverIdTag(): ?string
    {
        return $this->driverIdTag;
    }

    public function setDriverIdTag(?string $driverIdTag): void
    {
        $this->driverIdTag = $driverIdTag;
    }

    public function getOBDData()
    {
        return $this->OBDData;
    }

    public function setOBDData($OBDData): void
    {
        $this->OBDData = $OBDData;
    }

    /**
     * @return array|null
     */
    public function getBLEDataArray()
    {
        return $this->BLEDataArray;
    }

    /**
     * @param array|null $BLEDataArray
     */
    public function setBLEDataArray(?array $BLEDataArray): void
    {
        $this->BLEDataArray = $BLEDataArray;
    }

    /**
     * @return array|null
     */
    public function getTempAndHumidityData(): ?array
    {
        return $this->BLETempAndHumidityData;
    }

    /**
     * @param array|null $BLETempAndHumidityData
     */
    public function setBLETempAndHumidityData($BLETempAndHumidityData): void
    {
        $this->BLETempAndHumidityData = $BLETempAndHumidityData;
    }

    /**
     * @return null
     */
    public function getTempAndHumiditySensorIds()
    {
        $tempAndHumiditySensorIds = [];

        if ($tempAndHumidityData = $this->getTempAndHumidityData()) {
            /** @var TemperatureAndHumiditySensor $datum */
            foreach ($tempAndHumidityData as $datum) {
                $tempAndHumiditySensorIds[] = $datum->getBLESensorId();
            }
        }

        return $tempAndHumiditySensorIds;
    }

    /**
     * @return array|null
     */
    public function getBLEDriverSensorData()
    {
        return $this->BLEDriverSensorData;
    }

    /**
     * @param array|null $BLEDriverSensorData
     */
    public function setBLEDriverSensorData($BLEDriverSensorData): void
    {
        $this->BLEDriverSensorData = $BLEDriverSensorData;
    }

    /**
     * @return array|null
     */
    public function getBLESOSData()
    {
        return $this->BLESOSData;
    }

    /**
     * @param array|null $BLESOSData
     */
    public function setBLESOSData($BLESOSData): void
    {
        $this->BLESOSData = $BLESOSData;
    }

    /**
     * @return bool
     */
    public function isSOSAlarm(): bool
    {
        return $this->isSOSAlarm;
    }

    /**
     * @param bool $isSOSAlarm
     */
    public function setIsSOSAlarm(bool $isSOSAlarm): void
    {
        $this->isSOSAlarm = $isSOSAlarm;
    }

    /**
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->isSOSAlarm();
    }

    /**
     * @return BaseIOData|null
     */
    public function getIOData(): ?BaseIOData
    {
        return $this->IOData;
    }

    /**
     * @return BaseOutputData|null
     */
    public function getOutputData(): ?BaseOutputData
    {
        return $this->outputData;
    }

    /**
     * @return array|null
     */
    public function getIODataArray(): ?array
    {
        $outputData = $this->getOutputData() ? $this->getOutputData()->toArray() : [];
        $inputData = $this->getIOData() ? $this->getIOData()->toArray() : [];
        $IOData = array_merge($inputData, $outputData);
        
        return empty($IOData) ? null : $IOData;
    }

    /**
     * @param BaseIOData|null $IOData
     */
    public function setIOData(?BaseIOData $IOData): void
    {
        $this->IOData = $IOData;
    }

    /**
     * @param BaseOutputData|null $outputData
     */
    public function setOutputData(?BaseOutputData $outputData): void
    {
        $this->outputData = $outputData;
    }

    /**
     * @inheritDoc
     */
    public function getDTCVINData()
    {
        return $this->DTCVINData;
    }

    /**
     * @param DTCAndVIN|null $DTCVINData
     * @return self
     */
    public function setDTCVINData(?DTCAndVIN $DTCVINData): self
    {
        $this->DTCVINData = $DTCVINData;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDTCData(): bool
    {
        return boolval($this->getDTCVINData()?->getDTCData());
    }

    /**
     * @return bool
     */
    public function isVINData(): bool
    {
        return boolval($this->getDTCVINData()?->getVINData());
    }

    /**
     * @param string $textPayload
     * @return \DateTime
     * @throws \Exception
     */
    public static function formatDateTime(string $textPayload)
    {
        $datetime = (new \DateTime())::createFromFormat(self::DATETIME_FORMAT, $textPayload);

        if (!$datetime) {
            throw new \InvalidArgumentException("Invalid datetime: $textPayload, skipped.");
        }

        return $datetime;
    }

    /**
     * @param \DateTimeInterface $datetime
     * @return \DateTime
     */
    public static function encodeDateTime(\DateTimeInterface $datetime): string
    {
        $datetimeString = $datetime->format(self::DATETIME_FORMAT);

        if (!$datetimeString) {
            throw new \InvalidArgumentException("Invalid datetime format, skipped.");
        }

        return $datetimeString;
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatOverSpeedAlarmAndNetwork(string $textPayload)
    {
        // @todo not clear from doc

        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatGsensor(string $textPayload)
    {
        // @todo not clear from doc

        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatOther(string $textPayload)
    {
        // @todo not clear from doc

        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatRelayStatus(string $textPayload)
    {
        // @todo not clear from doc

        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatDragAlarm(string $textPayload)
    {
        // @todo not clear from doc

        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatAnalogInput(string $textPayload)
    {
        return DataHelper::formatIntegerAndFraction($textPayload, 2, 2);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatAnalogInputTLW2(string $textPayload): ?float
    {
        $value = self::formatValueWithFF($textPayload);

        return $value ? self::formatAnalogInput($value) : null;
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatReserve(string $textPayload)
    {
        // @todo not clear from doc
        return DataHelper::getBinaryFromHex($textPayload);
    }

    /**
     * @param string $textPayload
     * @return int|null
     */
    public static function formatBatteryVoltagePercentage(string $textPayload): ?int
    {
        switch (strtolower($textPayload)) {
            case '00':
                return 100;
            case 'ff':
                return null;
            default:
                return intval($textPayload);
        }
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatInternalBatteryVoltageTLW2(string $textPayload)
    {
        return DataHelper::formatIntegerAndFraction($textPayload, 2, 2);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatExternalVoltage(string $textPayload)
    {
        return DataHelper::formatIntegerAndFraction($textPayload, 2, 2);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatSolarPanelVoltage(string $textPayload)
    {
        return DataHelper::formatIntegerAndFraction($textPayload, 1, 1);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    public static function formatInternalBatteryVoltage(string $textPayload)
    {
        return DataHelper::formatIntegerAndFraction($textPayload, 1, 1);
    }

    /**
     * @param string $textPayload
     * @return null
     */
    public static function formatAcceleration(string $textPayload)
    {
        // @todo later if needed
        return null;
    }

    /**
     * @param int|null $value
     * @return float
     */
    public static function formatTemperatureMinus40(?int $value)
    {
        return $value ? $value - 40 : null;
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public static function formatValueWithFF(?string $value): ?string
    {
        switch (strtolower($value)) {
            case 'ff':
            case 'ffff':
            case 'ffffffff':
                return null;
            default:
                return $value;
        }
    }

    /**
     * @param string $value
     * @return int|null
     */
    public static function formatIntValueWithFF(string $value): ?int
    {
        $value = self::formatValueWithFF($value);

        return !is_null($value) ? hexdec($value) : null;
    }

    /**
     * @param string $value
     * @return int|null
     */
    public static function formatTempAndHumiditySensorBatteryVoltage(string $value): ?int
    {
        $value = self::formatIntValueWithFF($value);

        return !is_null($value) ? (200 + $value) * 10 : $value;
    }

    /**
     * @param string $value
     * @return float|null
     */
    public static function formatTempAndHumiditySensorTemperature(string $value): ?float
    {
        $value = self::formatValueWithFF($value);

        if (!$value) {
            return null;
        }

        $binaryValues = DataHelper::addZerosToStartOfString(DataHelper::getBinaryFromHex($value), 16);
        $sign = $binaryValues[0] == 1 ? '-' : '+';
        $numberValue = strval(bindec(substr($binaryValues, 1)));
        $number = DataHelper::formatIntegerAndFraction($numberValue, strlen($numberValue) - 2, 2);

        return floatval($sign . $number);
    }

    /**
     * @param string $value
     * @return float|null
     */
    public static function formatTempAndHumiditySensorHumidity(string $value): ?float
    {
        $value = self::formatIntValueWithFF($value);
        $valueAsString = strval($value);

        return !is_null($value)
            ? DataHelper::formatIntegerAndFraction($valueAsString, strlen($valueAsString) - 2, 2)
            : null;
    }

    /**
     * @param string $value
     * @return int|null
     */
    public static function formatTempAndHumiditySensorAmbientLightStatus(string $value): ?int
    {
        $value = self::formatValueWithFF($value);

        if (is_null($value)) {
            return null;
        }

        return hexdec($value) == 1
            ? TemperatureAndHumiditySensor::AMBIENT_LIGHT_STATUS_ON
            : TemperatureAndHumiditySensor::AMBIENT_LIGHT_STATUS_OFF;
    }

    /**
     * @param string $value
     * @return int|null
     */
    public static function formatTempAndHumiditySensorRSSIData(string $value): ?int
    {
        $value = self::formatValueWithFF($value);

        if (is_null($value)) {
            return null;
        }

        return hexdec($value) - 128;
    }

    /**
     * @param string $messageType
     * @param DeviceModel $deviceModel
     * @return int|null
     */
    public static function getMessageLengthByTypeAndModel(string $messageType, DeviceModel $deviceModel): ?int
    {
        switch ($deviceModel->getName()) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLD2_L:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                return self::getMessageLengthForTLW1AndTLD1AE($messageType);
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return self::getMessageLengthForTLW2($messageType);
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
                return self::getMessageLengthForTLD1DADE($messageType);
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                return self::getMessageLengthForTLP1($messageType);
            default:
                return null;
        }
    }

    /**
     * @param string $messageType
     * @return int
     */
    public static function getMessageLengthForTLW1AndTLD1AE(string $messageType): ?int
    {
        switch ($messageType) {
            case self::POSITION_MESSAGE_TYPE:
                return Position::getPacketLength();
            case self::POSITION_MESSAGE_TLD2_L_TYPE:
                return PositionTLD2L::getPacketLength();
            default:
                return null;
        }
    }

    /**
     * @param string $messageType
     * @return int
     */
    public static function getMessageLengthForTLW2(string $messageType): ?int
    {
        switch ($messageType) {
            case self::POSITION_MESSAGE_TLW2_TYPE:
                return PositionTLW212BL::getPacketLength();
            default:
                return null;
        }
    }

    /**
     * @param string $messageType
     * @return int
     */
    public static function getMessageLengthForTLD1DADE(string $messageType): ?int
    {
        switch ($messageType) {
            case self::POSITION_MESSAGE_TYPE:
                return PositionTLD1DADE::getPacketLength();
            default:
                return null;
        }
    }

    /**
     * @param string $messageType
     * @return int
     */
    public static function getMessageLengthForTLP1(string $messageType): ?int
    {
        switch ($messageType) {
            case self::POSITION_MESSAGE_TYPE:
                return PositionTLP1::getPacketLength();
            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getNetworkData()
    {
        return $this->networkData;
    }

    /**
     * @param BaseNetwork|null $networkData
     * @return Data
     */
    public function setNetworkData(?BaseNetwork $networkData): Data
    {
        $this->networkData = $networkData;

        return $this;
    }

    /**
     * @param int|null $satellites
     * @return Data
     */
    public function setSatellites(?int $satellites): Data
    {
        $this->satellites = $satellites;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSatellites(): ?int
    {
        return $this->satellites;
    }

    /**
     * @return float|null
     */
    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    /**
     * @param float|null $speed
     */
    public function setSpeed(?float $speed): void
    {
        $this->speed = $speed;
    }

    /**
     * @return bool
     */
    public function isJammerAlarm(): bool
    {
        return $this->isJammerAlarm;
    }

    /**
     * @param bool $isJammerAlarm
     */
    public function setIsJammerAlarm(bool $isJammerAlarm): void
    {
        $this->isJammerAlarm = $isJammerAlarm;
    }

    /**
     * @return bool
     */
    public function isJammerAlarmStarted(): bool
    {
        return $this->getAlarmData() && $this->getAlarmData()->isJammerAlarmStarted();
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isValueNullableFF(string $value): bool
    {
        $chars = str_split($value);

        foreach ($chars as $char) {
            if ($char != 'f') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $value
     * @return array
     */
    public static function format3AxisesFromHex(string $value): array
    {
        $axisLen = strlen($value) / 3;

        return [
            'x' => DataHelper::hexToSignedInt(substr($value, 0, $axisLen)),
            'y' => DataHelper::hexToSignedInt(substr($value, $axisLen, $axisLen)),
            'z' => DataHelper::hexToSignedInt(substr($value, $axisLen * 2, $axisLen)),
        ];
    }

    public function isAccidentHappened(): bool
    {
        return $this->getAccidentData() && $this->getAccidentData()->isHappened();
    }

    public function getOneWireData(): ?BaseOneWire
    {
        return $this->oneWireData;
    }

    public function setOneWireData(?BaseOneWire $oneWireData): void
    {
        $this->oneWireData = $oneWireData;
    }

    public function getDriverFOBId(): ?string
    {
        return $this->driverFOBId;
    }

    public function setDriverFOBId(?string $driverFOBId): void
    {
        $this->driverFOBId = $driverFOBId;
    }
}
