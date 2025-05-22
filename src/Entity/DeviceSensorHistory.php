<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'device_sensor_history')]
#[ORM\Index(name: 'device_sensor_history_device_id_sensor_id_installed_at_index', columns: ['device_id', 'sensor_id', 'installed_at'])]
#[ORM\Index(name: 'device_sensor_history_device_id_sensor_id_uninstalled_at_index', columns: ['device_id', 'sensor_id', 'uninstalled_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceSensorHistoryRepository')]
#[ORM\HasLifecycleCallbacks]
class DeviceSensorHistory extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'device',
        'sensor',
        'installedAt',
        'uninstalledAt',
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'deviceId',
        'sensorId',
        'installedAt',
        'uninstalledAt',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Device
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'deviceSensorHistories')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $device;

    /**
     * @var Sensor
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sensor', inversedBy: 'deviceSensorHistories')]
    #[ORM\JoinColumn(name: 'sensor_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $sensor;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'installed_at', type: 'datetime')]
    private $installedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'uninstalled_at', type: 'datetime', nullable: true)]
    private $uninstalledAt;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->device = $fields['device'] ?? null;
        $this->sensor = $fields['sensor'] ?? null;
        $this->installedAt = $fields['installedAt'] ?? Carbon::now('UTC');
        $this->uninstalledAt = $fields['uninstalledAt'] ?? null;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()->toArray(Device::SIMPLE_FIELDS);
        }
        if (in_array('sensor', $include, true)) {
            $data['sensor'] = $this->getSensor()->toArray();
        }
        if (in_array('sensorId', $include, true)) {
            $data['sensorId'] = $this->getSensor()->getSensorId();
        }
        if (in_array('installedAt', $include, true)) {
            $data['installedAt'] = $this->formatDate($this->getInstalledAt());
        }
        if (in_array('uninstalledAt', $include, true)) {
            $data['uninstalledAt'] = $this->formatDate($this->getUninstalledAt());
        }

        return $data;
    }

    /**
     * @param User $user
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_EXPORT_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice() ? $this->getDevice()->toArray(Device::SIMPLE_FIELDS) : null;
        }
        if (in_array('sensorId', $include, true)) {
            $data['sensorId'] = $this->getSensor()->getSensorId();
        }
        if (in_array('installedAt', $include, true)) {
            $data['installedAt'] = $this->formatDate(
                $this->getInstalledAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }
        if (in_array('uninstalledAt', $include, true)) {
            $data['uninstalledAt'] = $this->formatDate(
                $this->getUninstalledAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }

        return $data;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->getDevice()->getVehicle() ?: null;
    }

    /**
     * @return int
     */
    public function getDeviceId(): int
    {
        return $this->getDevice()->getId();
    }

    /**
     * @param Device $device
     * @return self
     */
    public function setDevice(Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getDevice()->getVehicle() ? $this->getDevice()->getVehicle()->getId() : null;
    }

    /**
     * @return string
     */
    public function getSensorId(): string
    {
        return $this->getSensor()->getSensorId();
    }

    /**
     * @return Sensor
     */
    public function getSensor(): Sensor
    {
        return $this->sensor;
    }

    /**
     * @param Sensor $sensor
     * @return self
     */
    public function setSensor(Sensor $sensor): self
    {
        $this->sensor = $sensor;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInstalledAt(): \DateTime
    {
        return $this->installedAt;
    }

    /**
     * @param \DateTime $installedAt
     * @return self
     */
    public function setInstalledAt(\DateTime $installedAt): self
    {
        $this->installedAt = $installedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUninstalledAt(): ?\DateTime
    {
        return $this->uninstalledAt;
    }

    /**
     * @param \DateTime|null $uninstalledAt
     * @return self
     */
    public function setUninstalledAt(?\DateTime $uninstalledAt): self
    {
        $this->uninstalledAt = $uninstalledAt;

        return $this;
    }
}

