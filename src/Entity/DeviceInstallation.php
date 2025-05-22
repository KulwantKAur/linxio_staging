<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DeviceInstallation
 */
#[ORM\Table(name: 'device_installation')]
#[ORM\Index(name: 'device_installation_vehicle_id_installdate_uninstalldate_index', columns: ['vehicle_id', 'installdate', 'uninstalldate'])]
#[ORM\Index(name: 'device_installation_device_id_installdate_uninstalldate_index', columns: ['device_id', 'installdate', 'uninstalldate'])]
#[ORM\Index(name: 'device_installation_is_odometer_synced_index', columns: ['is_odometer_synced'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceInstallationRepository')]
class DeviceInstallation extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'deviceId',
        'vehicleId',
        'vehicle',
        'installDate',
        'uninstallDate',
        'files',
        'odometer',
        'device'
    ];

    public const SIMPLE_DISPLAY_VALUES = [
        'deviceId',
        'vehicleId',
        'installDate',
        'uninstallDate',
        'odometer'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Device', inversedBy: 'deviceInstallations')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $device;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'installDate', type: 'datetime')]
    private $installDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'uninstallDate', type: 'datetime', nullable: true)]
    private $uninstallDate;

    /**
     * @var ArrayCollection
     */
    #[ORM\JoinTable(name: 'device_installation_file')]
    #[ORM\JoinColumn(name: 'device_installation_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'file_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'File')]
    private $files;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'odometer', type: 'bigint', nullable: true)]
    private $odometer;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_odometer_synced', type: 'boolean', options: ['default' => '0'])]
    private $isOdometerSynced = false;

    public function __construct(array $fields)
    {
        $this->device = $fields['device'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->installDate = $fields['installDate'] ?? null;
        $this->uninstallDate = $fields['uninstallDate'] ?? null;
        $this->odometer = $fields['odometer'] ?? null;
        $this->files = new ArrayCollection();
    }

    public function toArray($include = [], ?User $user = null): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDevice()->getId();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->vehicle->getId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()->toArray(array_merge(Device::SIMPLE_FIELDS, ['model.name']), $user);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->vehicle->toArray(Vehicle::DISPLAYED_VALUES);
        }
        if (in_array('installDate', $include, true)) {
            $data['installDate'] = $this->formatDate($this->installDate);
        }
        if (in_array('uninstallDate', $include, true)) {
            $data['uninstallDate'] = $this->formatDate($this->uninstallDate);
        }
        if (in_array('files', $include, true)) {
            $data['files'] = $this->getFilesArray();
        }
        if (in_array('odometer', $include, true)) {
            $data['odometer'] = $this->getOdometer();
        }
        if (in_array('isOdometerSynced', $include, true)) {
            $data['isOdometerSynced'] = $this->isOdometerSynced();
        }

        return $data;
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
     * Set device.
     *
     * @param Device $device
     *
     * @return DeviceInstallation
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device.
     *
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return DeviceInstallation
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set installDate.
     *
     * @param \DateTime $installDate
     *
     * @return DeviceInstallation
     */
    public function setInstallDate($installDate)
    {
        $this->installDate = $installDate;

        return $this;
    }

    /**
     * Get installDate.
     *
     * @return \DateTime
     */
    public function getInstallDate()
    {
        return $this->installDate;
    }

    /**
     * Get formatted installDate.
     *
     * @param string $format
     *
     * @return \DateTime|mixed|null
     */
    public function getFormattedInstallDate(string $format)
    {
        return $this->installDate->format($format);
    }

    /**
     * Set uninstallDate.
     *
     * @param \DateTime|null $uninstallDate
     *
     * @return DeviceInstallation
     */
    public function setUninstallDate($uninstallDate = null)
    {
        $this->uninstallDate = $uninstallDate;

        return $this;
    }

    /**
     * Get uninstallDate.
     *
     * @return \DateTime|null
     */
    public function getUninstallDate()
    {
        return $this->uninstallDate;
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getFilesArrayIds(): array
    {
        return array_values(
            array_map(
                static function (File $file) {
                    return $file->getId();
                },
                $this->files->toArray()
            )
        );
    }

    /**
     * @param File $file
     * @return DeviceInstallation
     */
    public function addFile(File $file): DeviceInstallation
    {
        $this->files->add($file);

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function removeFiles(array $ids)
    {
        $this->files = array_filter(
            $this->files->toArray(),
            static function (File $file) use ($ids) {
                return !in_array($file->getId(), $ids);
            }
        );
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getFilesArray(array $fields = []): array
    {
        return array_values(
            array_map(
                static function (File $file) use ($fields) {
                    $file->setPath(LocalFileService::INSTALLATION_PUBLIC_PATH);
                    return $file->toArray($fields);
                },
                $this->files->toArray()
            )
        );
    }

    /**
     * Set odometer.
     *
     * @param int|null $odometer
     *
     * @return DeviceInstallation
     */
    public function setOdometer(?int $odometer)
    {
        $this->odometer = $odometer;

        return $this;
    }

    /**
     * Get odometer.
     *
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return bool
     */
    public function isOdometerSynced(): bool
    {
        return $this->isOdometerSynced;
    }

    /**
     * @param bool $isOdometerSynced
     * @return DeviceInstallation
     */
    public function setIsOdometerSynced(bool $isOdometerSynced): self
    {
        $this->isOdometerSynced = $isOdometerSynced;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUninstalled(): bool
    {
        return boolval($this->getUninstallDate());
    }
}
