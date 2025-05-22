<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'asset_sensor_history')]
#[ORM\Index(name: 'asset_sensor_history_asset_id_sensor_id_installed_at_index', columns: ['asset_id', 'sensor_id', 'installed_at'])]
#[ORM\Index(name: 'asset_sensor_history_asset_id_sensor_id_uninstalled_at_index', columns: ['asset_id', 'sensor_id', 'uninstalled_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\AssetSensorHistoryRepository')]
#[ORM\HasLifecycleCallbacks]
class AssetSensorHistory extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'asset',
        'sensor',
        'installedAt',
        'uninstalledAt',
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'assetId',
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
     * @var Asset
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Asset', inversedBy: 'assetSensorHistories')]
    #[ORM\JoinColumn(name: 'asset_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $asset;

    /**
     * @var Sensor
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sensor')]
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
        $this->asset = $fields['asset'] ?? null;
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
        if (in_array('assetId', $include, true)) {
            $data['assetId'] = $this->getAssetId();
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset()->toArray();
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
        if (in_array('assetId', $include, true)) {
            $data['assetId'] = $this->getAssetId();
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset()->toArray();
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
    public function getDevice(): ?Device
    {
        return $this->getSensor()->getDeviceSensorForAsset()
            ? $this->getSensor()->getDeviceSensorForAsset()->getDevice()
            : null;
    }

    /**
     * @return Device|null
     */
    public function getDeviceWithDeleted(): ?Device
    {
        return $this->getSensor()->getDeviceSensorHistoryWithDeletedByDate($this->getInstalledAt())
            ? $this->getSensor()->getDeviceSensorHistoryWithDeletedByDate($this->getInstalledAt())->getDevice()
            : null;
    }

    /**
     * @return int
     */
    public function getDeviceId(): ?int
    {
        return $this->getSensor()->getDeviceSensorForAsset()
            ? $this->getSensor()->getDeviceSensorForAsset()->getDevice()->getId()
            : null;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicleWithDeleted(): ?Vehicle
    {
        return $this->getSensor()->getDeviceSensorHistoryWithDeletedByDate($this->getInstalledAt())
            ? $this->getSensor()
                ->getDeviceSensorHistoryWithDeletedByDate($this->getInstalledAt())->getVehicle()
            : null;
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
     * @return bool
     */
    public function isUninstalled(): bool
    {
        return boolval($this->getUninstalledAt());
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

    /**
     * @return Asset
     */
    public function getAsset(): Asset
    {
        return $this->asset;
    }

    /**
     * @return int
     */
    public function getAssetId(): int
    {
        return $this->getAsset()->getId();
    }

    /**
     * @param Asset $asset
     * @return self
     */
    public function setAsset(Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }
}

