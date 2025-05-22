<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DriverHistory
 *
 *
 */
#[ORM\Table(name: 'driver_history')]
#[ORM\Index(name: 'driver_history_vehicle_id_startdate_finishdate_index', columns: ['vehicle_id', 'startdate', 'finishdate'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DriverHistoryRepository')]
class DriverHistory extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'driver',
        'vehicle',
        'startDate',
        'finishDate'
    ];

    /**
     * DriverHistory constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->driver = $fields['driver'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->startDate = $fields['startDate'] ?? null;
        $this->finishDate = $fields['finishDate'] ?? null;
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

        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->driver->toArray(User::DISPLAYED_VALUES);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->vehicle->toArray(array_merge(Vehicle::DISPLAYED_VALUES, ['driver']));
        }
        if (in_array('startDate', $include, true)) {
            $data['startDate'] = $this->formatDate($this->startDate);
        }
        if (in_array('finishDate', $include, true)) {
            $data['finishDate'] = $this->formatDate($this->finishDate);
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', nullable: false)]
    private $driver;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'driverHistory')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: false)]
    private $vehicle;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'startDate', type: 'datetime')]
    private $startDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'finishDate', type: 'datetime', nullable: true)]
    private $finishDate;


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
     * Set driver.
     *
     * @param User $driver
     *
     * @return DriverHistory
     */
    public function setDriver(User $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver.
     *
     * @return User
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return DriverHistory
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
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return DriverHistory
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set finishDate.
     *
     * @param \DateTime|null $finishDate
     *
     * @return DriverHistory
     */
    public function setFinishDate($finishDate = null)
    {
        $this->finishDate = $finishDate;

        return $this;
    }

    /**
     * Get finishDate.
     *
     * @return \DateTime|null
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }
}
