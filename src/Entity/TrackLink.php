<?php

namespace App\Entity;

use App\Util\StringHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackLink
 */
#[ORM\Table(name: 'track_link')]
#[ORM\Entity(repositoryClass: 'App\Repository\TrackLinkRepository')]
class TrackLink extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'vehicle',
        'device',
        'driverName',
        'message',
        'dateFrom',
        'dateTo',
        'hash',
        'createdAt',
        'provider'
    ];

    public function __construct(array $fields)
    {
        $this->vehicle = $fields['vehicle'];
        $this->driverName = $fields['driverName'];
        $this->message = $fields['message'];
        $this->dateFrom = $fields['dateFrom'];
        $this->dateTo = $fields['dateTo'];
        $this->hash = StringHelper::generateRandomString(8);
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'];
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()->toArray(Vehicle::LIST_DISPLAY_VALUES);
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDeviceData();
        }
        if (in_array('provider', $include, true)) {
            $data['provider'] = $this->getProvider();
        }
        if (in_array('driverName', $include, true)) {
            $data['driverName'] = $this->getDriverName();
        }
        if (in_array('message', $include, true)) {
            $data['message'] = $this->getMessage();
        }
        if (in_array('hash', $include, true)) {
            $data['hash'] = $this->getHash();
        }
        if (in_array('dateFrom', $include, true)) {
            $data['dateFrom'] = $this->formatDate($this->getDateFrom());
        }
        if (in_array('dateTo', $include, true)) {
            $data['dateTo'] = $this->formatDate($this->getDateTo());
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'driver_name', type: 'string', length: 255, nullable: true)]
    private $driverName;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'message', type: 'text', nullable: true)]
    private $message;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'hash', type: 'string', length: 255, nullable: false)]
    private $hash;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date_from', type: 'datetime')]
    private $dateFrom;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date_to', type: 'datetime')]
    private $dateTo;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;


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
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return TrackLink
     */
    public function setVehicle(Vehicle $vehicle)
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
     * Set driverName.
     *
     * @param string|null $driverName
     *
     * @return TrackLink
     */
    public function setDriverName($driverName = null)
    {
        $this->driverName = $driverName;

        return $this;
    }

    /**
     * Get driverName.
     *
     * @return string|null
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * Set message.
     *
     * @param string|null $message
     *
     * @return TrackLink
     */
    public function setMessage($message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $hash
     * @return $this
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string|null
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Set dateFrom.
     *
     * @param \DateTime $dateFrom
     *
     * @return TrackLink
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * Get dateFrom.
     *
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * Set dateTo.
     *
     * @param \DateTime $dateTo
     *
     * @return TrackLink
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * Get dateTo.
     *
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackLink
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
     * Set createdBy.
     *
     * @param int $createdBy
     *
     * @return TrackLink
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceData()
    {
        return $this->getVehicle()->getDevice()
            ? $this->getVehicle()->getDevice()->toArray(array_merge(Device::SIMPLE_FIELDS, ['trackerData']))
            : null;
    }

    /**
     * @return |null
     */
    public function getProvider()
    {
        $providerSetting = $this->getCreatedBy()->getSettingByName(Setting::MAP_PROVIDER);

        return $providerSetting ? $providerSetting->getValue() : null;
    }
}
