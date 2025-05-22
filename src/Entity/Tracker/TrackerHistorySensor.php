<?php

namespace App\Entity\Tracker;

use App\Entity\Asset;
use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\DeviceSensor;
use App\Entity\DeviceSensorType;
use App\Entity\DeviceVendor;
use App\Entity\Route;
use App\Entity\Sensor;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Table(name: 'tracker_history_sensor')]
#[ORM\Index(name: 'tracker_history_sensor_device_id_created_at_index', columns: ['device_id', 'created_at'])]
#[ORM\Index(name: 'tracker_history_sensor_device_id_occurred_at_index', columns: ['device_id', 'occurred_at'])]
#[ORM\Index(name: 'tracker_history_sensor_vehicle_id_created_at_index', columns: ['vehicle_id', 'created_at'])]
#[ORM\Index(name: 'tracker_history_sensor_vehicle_id_occurred_at_index', columns: ['vehicle_id', 'occurred_at'])]
#[ORM\Index(name: 'tracker_history_sensor_occurred_at_index', columns: ['occurred_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistorySensorRepository')]
class TrackerHistorySensor extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'deviceId',
        'vehicleId',
        'deviceSensorId',
        'deviceSensorBLEId',
        'deviceSensorLabel',
        'value',
        'occurredAt',
        'temperature',
        'humidity',
        'batteryPercentage',
        'light',
        'status',
        'RSSI',
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'id',
        'deviceSensorId',
        'deviceSensorLabel',
        'BLESensorId',
        'occurredAt',
        'value',
        'lastPosition',
        'batteryPercentage',
        'temperature',
        'humidity',
    ];

    public const DEVICE_SENSOR_HISTORY_DISPLAY_VALUES = [
        'id',
        'vehicleId',
        'value',
        'occurredAt',
        'temperature',
        'humidity',
        'batteryPercentage',
        'light',
        'status'
    ];

    public const DEFAULT_TEMP_AND_HUMIDITY_VALUES = [
        'temperature',
        'humidity',
        'batteryPercentage',
        'light',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Device|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerSensorRecords')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'occurred_at', type: 'datetime')]
    private $occurredAt;

    /**
     * @var TrackerHistory|null
     *
     *
     */
    #[ORM\OneToOne(targetEntity: 'TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerHistory;

    /**
     * @var TrackerPayload|null
     */
    #[ORM\ManyToOne(targetEntity: 'TrackerPayload', inversedBy: 'trackerHistorySensor')]
    #[ORM\JoinColumn(name: 'tracker_payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerPayload;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var array|null
     *
     *
     */
    #[ORM\Column(name: 'data', type: 'json', nullable: true)]
    private $data;

    /**
     * @var DeviceSensor
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceSensor', inversedBy: 'trackerHistoriesSensor')]
    #[ORM\JoinColumn(name: 'device_sensor_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $deviceSensor;

    /**
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerSensorHistories')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var string|null
     */
    private $lastPosition;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_nullable_data', type: 'boolean', options: ['default' => '0'])]
    private $isNullableData = false;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @return string|null
     * @throws \Exception
     */
    private function getTempAndHumidityValueByVendor(): ?string
    {
        switch ($this->getDeviceVendorName()) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
                $value = !is_null($this->getData()['temperature'])
                && !is_null($this->getData()['humidity'])
                    ? $this->getData()['temperature'] . '°C, ' . $this->getData()['humidity'] . '%, ' .
                    $this->getAmbientLightStatus()
                    : null;
                break;
            default:
                $value = null;
        }

        return $value;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    private function getBatteryVoltageByDeviceVendor(): ?string
    {
        switch ($this->getDeviceVendorName()) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
                $value = !is_null($this->getData()['sensorBatteryVoltage'])

                    ? $this->getData()['sensorBatteryVoltage'] / 1000 . 'V'
                    : null;
                break;
            default:
                $value = null;
        }

        return $value;
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    private function getBatteryPercentageByDeviceVendor(): ?int
    {
        switch ($this->getDeviceVendorName()) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
                $value = !is_null($this->getData()['sensorBatteryPercentage'])
                    ? $this->getData()['sensorBatteryPercentage']
                    : null;
                break;
            default:
                $value = null;
        }

        return $value;
    }

    /**
     * @return string|null
     */
    private function getDeviceSensorTypeName(): ?string
    {
        return $this->getDeviceSensor() ? $this->getDeviceSensor()->getTypeName() : null;
    }

    /**
     * @param array $data
     * @param array $include
     * @return array
     */
    private function addExtraFieldsBySensorType(array $data, array $include): array
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                if (empty($include)) {
                    $include = self::DEFAULT_TEMP_AND_HUMIDITY_VALUES;
                }
                if (in_array('temperature', $include, true)) {
                    $data['temperature'] = $this->getTemperature();
                }
                if (in_array('humidity', $include, true)) {
                    $data['humidity'] = $this->getHumidity();
                }
                if (in_array('batteryPercentage', $include, true)) {
                    $data['batteryPercentage'] = $this->getBatteryPercentage();
                }
                if (in_array('light', $include, true)) {
                    $data['light'] = $this->getLight();
                }
                if (in_array('status', $include, true)) {
                    $data['status'] = $this->getStatus();
                }
                if (in_array('RSSI', $include, true)) {
                    $data['RSSI'] = $this->getRSSI();
                }

                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $include
     * @return array
     */
    private function addExtraFieldsForExportBySensorType(array $data, array $include = []): array
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                if (empty($include)) {
                    $include = self::DEFAULT_TEMP_AND_HUMIDITY_VALUES;
                }
                if (in_array('temperature', $include, true)) {
                    $data['temperature'] = $this->getData()['temperature'] ?? null;
                }
                if (in_array('humidity', $include, true)) {
                    $data['humidity'] = $this->getData()['humidity'] ?? null;
                }

                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * @return array|string|null
     * @throws \Exception
     */
    public function getValueBySensorType()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                $value = $this->getTempAndHumidityValueByVendor();

                break;
            default:
                $value = implode(', ', $this->getData());
                break;
        }

        return $value;
    }

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return TrackerPayload|null
     */
    public function getTrackerPayload(): ?TrackerPayload
    {
        return $this->trackerPayload;
    }

    /**
     * @param TrackerPayload|null $trackerPayload
     */
    public function setTrackerPayload(?TrackerPayload $trackerPayload): void
    {
        $this->trackerPayload = $trackerPayload;
    }

    /**
     * @param array $include
     * @param User|null $user
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }
        if (in_array('deviceSensorId', $include, true)) {
            $data['deviceSensorId'] = $this->getDeviceSensorId();
        }
        if (in_array('deviceSensorBLEId', $include, true)) {
            $data['deviceSensorBLEId'] = $this->getDeviceSensorBLEId();
        }
        if (in_array('deviceSensorLabel', $include, true)) {
            $data['deviceSensorLabel'] = $this->getDeviceSensorLabel();
        }
        if (in_array('data', $include, true)) {
            $data['data'] = $this->getData();
        }
        if (in_array('value', $include, true)) {
            $data['value'] = $this->getValueBySensorType();
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->formatDate($this->getOccurredAt());
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('lastPosition', $include, true)) {
            $data['lastPosition'] = $this->getLastPosition();
        }
        if (in_array('batteryVoltage', $include, true)) {
            $data['batteryVoltage'] = $this->getBatteryVoltageByDeviceVendor();
        }
        if (in_array('batteryPercentage', $include, true)) {
            $data['batteryPercentage'] = $this->getBatteryPercentageByDeviceVendor();
        }

        return $this->addExtraFieldsBySensorType($data, $include);
    }

    /**
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @return int|null
     */
    public function getDeviceId()
    {
        return $this->getDevice() ? $this->getDevice()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceModelName()
    {
        return $this->getDevice() ? $this->getDevice()->getModelName() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceVendorName()
    {
        return $this->getDevice() ? $this->getDevice()->getVendorName() : null;
    }

    /**
     * @param Device|null $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return \DateTimeInterface|\DateTime
     */
    public function getOccurredAt()
    {
        return $this->occurredAt;
    }

    /**
     * @param \DateTimeInterface $occurredAt
     */
    public function setOccurredAt($occurredAt): void
    {
        $this->occurredAt = $occurredAt;
    }

    /**
     * @return TrackerHistory|null
     */
    public function getTrackerHistory(): ?TrackerHistory
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory|null $trackerHistory
     */
    public function setTrackerHistory(?TrackerHistory $trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return DeviceSensor
     */
    public function getDeviceSensor(): DeviceSensor
    {
        return $this->deviceSensor;
    }

    /**
     * @return int|null
     */
    public function getDeviceSensorId(): ?int
    {
        return $this->getDeviceSensor() ? $this->getDeviceSensor()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceSensorBLEId(): ?string
    {
        return $this->getDeviceSensor() ? $this->getDeviceSensor()->getSensorBLEId() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceSensorLabel(): ?string
    {
        return $this->getDeviceSensor() ? $this->getDeviceSensor()->getLabel() : null;
    }

    /**
     * @param DeviceSensor $deviceSensor
     */
    public function setDeviceSensor(DeviceSensor $deviceSensor): void
    {
        $this->deviceSensor = $deviceSensor;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @param Vehicle|null $vehicle
     */
    public function setVehicle(?Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getVehicle() ? $this->getVehicle()->getId() : null;
    }

    /**
     * @param User $user
     * @param array $include
     * @param string|null $sensorTypeName
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null, ?string $sensorTypeName = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_EXPORT_VALUES;
        }
        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }
        if (in_array('deviceSensorId', $include, true)) {
            $data['deviceSensorId'] = $this->getDeviceSensorId();
        }
        if (in_array('deviceSensorLabel', $include, true)) {
            $data['deviceSensorLabel'] = $this->getDeviceSensorLabel();
        }
        if (in_array('BLESensorId', $include, true)) {
            $data['BLESensorId'] = $this->getDeviceSensorBLEId();
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->formatDate(
                $this->getOccurredAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }
        if (in_array('data', $include, true)) {
            $data['data'] = $this->getData();
        }
        if (in_array('value', $include, true)) {
            $data['value'] = $this->getValueBySensorType();
        }
        if (in_array('lastPosition', $include, true)) {
            $data['lastPosition'] = $this->getLastPosition();
        }
        if (in_array('batteryVoltage', $include, true)) {
            $data['batteryVoltage'] = $this->getBatteryVoltageByDeviceVendor();
        }
        if (in_array('batteryPercentage', $include, true)) {
            $data['batteryPercentage'] = $this->getBatteryPercentageByDeviceVendor();
        }

        return $this->addExtraFieldsForExportBySensorType($data, $include);
    }

    /**
     * @return string|null
     */
    public function getLastPosition(): ?string
    {
        return $this->lastPosition;
    }

    /**
     * @param string|null $lastPosition
     */
    public function setLastPosition(?string $lastPosition): void
    {
        $this->lastPosition = $lastPosition;
    }

    /**
     * @param Route $lastRoute
     * @param TranslatorInterface $translator
     */
    public function setLastPositionFromRoute(Route $lastRoute, TranslatorInterface $translator): void
    {
        $position = $lastRoute->getType() == Route::TYPE_STOP
            ? $lastRoute->getAddress()
            : $translator->trans('entities.device_sensor.vehicle_is_moving');

        $this->setLastPosition($position);
    }

    /**
     * @return int
     */
    public function getAmbientLightStatus(): int
    {
        return isset($this->getData()['ambientLightStatus']) ? $this->getData()['ambientLightStatus'] : 0;
    }

    /**
     * @return bool
     */
    public function isNullableData(): bool
    {
        return $this->isNullableData;
    }

    /**
     * @param bool $isNullableData
     */
    public function setIsNullableData(bool $isNullableData): void
    {
        $this->isNullableData = $isNullableData;
    }

    public function getTemperature()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return $this->getData()['temperature'] ?? null;
            default:
                return null;
        }
    }

    public function getRSSI()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return $this->getData()['RSSI'] ?? null;
            default:
                return null;
        }
    }

    public function getHumidity()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return $this->getData()['humidity'] ?? null;
            default:
                return null;
        }
    }

    public function getBatteryPercentage()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return $this->getData()['sensorBatteryPercentage'] ?? null;
            default:
                return null;
        }
    }

    public function getLight()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return $this->getData()['ambientLightStatus'] ?? null;
            default:
                return null;
        }
    }

    public function getStatus()
    {
        switch ($this->getDeviceSensorTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                return !$this->isNullableData();
            default:
                return null;
        }
    }

    /**
     * @return Sensor
     */
    public function getSensor(): Sensor
    {
        return $this->getDeviceSensor()->getSensor();
    }

    /**
     * @return Asset
     */
    public function getAsset(): Asset
    {
        return $this->getDeviceSensor()->getSensor()->getAsset();
    }

    /**
     * @return int|null
     */
    public function getAssetId(): ?int
    {
        return $this->getDeviceSensor()->getSensor()->getAssetId()
            ? $this->getDeviceSensor()->getSensor()->getAssetId()
            : null;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     */
    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
