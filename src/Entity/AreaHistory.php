<?php

namespace App\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * AreaHistory
 */
#[ORM\Table(name: 'area_history')]
#[ORM\Index(name: 'area_history_vehicle_id_arrived_index', columns: ['vehicle_id', 'arrived'])]
#[ORM\Index(name: 'area_history_vehicle_id_departed_index', columns: ['vehicle_id', 'departed'])]
#[ORM\Entity(repositoryClass: 'App\Repository\AreaHistoryRepository')]
#[ORM\EntityListeners(['App\EventListener\AreaHistory\AreaHistoryEntityListener'])]
class AreaHistory extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'area',
        'vehicle',
        'driverArrived',
        'driverDeparted',
        'arrived',
        'departed'
    ];

    public const VEHICLE_DISPLAY_VALUES = [
        'area',
        'driverArrived',
        'driverDeparted',
        'arrived',
        'departed'
    ];

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('area', $include, true)) {
            $data['area'] = $this->getArea()->toArray(Area::AREA_HISTORY_DISPLAY_VALUES);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()->toArray();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDeviceData();
        }
        if (in_array('driverArrived', $include, true)) {
            $data['driverArrived'] = $this->getDriverArrivedData();
        }
        if (in_array('driverDeparted', $include, true)) {
            $data['driverDeparted'] = $this->getDriverDepartedData();
        }
        if (in_array('arrived', $include, true)) {
            $data['arrived'] = $this->formatDate($this->getArrived());
        }
        if (in_array('departed', $include, true)) {
            $data['departed'] = $this->formatDate($this->getDeparted());
        }

        return $data;
    }

    public function __construct(array $fields)
    {
        $this->area = $fields['area'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->driverArrived = $fields['driverArrived'] ?? null;
        $this->driverDeparted = $fields['driverDeparted'] ?? null;
        $this->arrived = $fields['arrived'] ?? null;
        $this->departed = $fields['departed'] ?? null;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Area
     */
    #[ORM\ManyToOne(targetEntity: 'Area', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'area_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $area;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', fetch: 'EXTRA_LAZY', inversedBy: 'areaHistories')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $vehicle;

    private $driverArrived;

    private $driverDeparted;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'arrived', type: 'datetime')]
    private $arrived;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'departed', type: 'datetime', nullable: true)]
    private $departed;

    private $em;


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
     * Set area.
     *
     * @param Area $area
     *
     * @return AreaHistory
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area.
     *
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return AreaHistory
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
     * Set driverArrived.
     *
     * @param User $driverArrived
     *
     * @return AreaHistory
     */
    public function setDriverArrived($driverArrived)
    {
        $this->driverArrived = $driverArrived;

        return $this;
    }

    /**
     * Set driverDeparted.
     *
     * @param User $driverDeparted
     *
     * @return AreaHistory
     */
    public function setDriverDeparted($driverDeparted)
    {
        $this->driverDeparted = $driverDeparted;

        return $this;
    }

    /**
     * Get driverArrived.
     *
     * @return User
     */
    public function getDriverArrived()
    {
        if ($this->driverArrived) {
            return $this->driverArrived;
        }

        $driverHistoryArrived = $this->em->getRepository(DriverHistory::class)
            ->findDriverByDateRange($this->getVehicle(), $this->getArrived());

        if ($driverHistoryArrived) {
            $this->driverArrived = $driverHistoryArrived->getDriver();
        }

        return $this->driverArrived;
    }

    /**
     * Get driverDeparted.
     *
     * @return User
     */
    public function getDriverDeparted()
    {
        if ($this->driverDeparted) {
            return $this->driverDeparted;
        }

        $driverHistoryDeparted = $this->em->getRepository(DriverHistory::class)
            ->findDriverByDateRange($this->getVehicle(), $this->getArrived());

        if ($driverHistoryDeparted) {
            $this->driverDeparted = $driverHistoryDeparted->getDriver();
        }

        return $this->driverDeparted;
    }

    /**
     * @return array|null
     */
    public function getDriverArrivedData()
    {
        return $this->driverArrived ? $this->driverArrived->toArray(User::DISPLAYED_VALUES) : null;
    }

    /**
     * @return array|null
     */
    public function getDriverDepartedData()
    {
        return $this->driverDeparted ? $this->driverDeparted->toArray(User::DISPLAYED_VALUES) : null;
    }

    /**
     * Set arrived.
     *
     * @param \DateTime $arrived
     *
     * @return AreaHistory
     */
    public function setArrived($arrived)
    {
        $this->arrived = $arrived;

        return $this;
    }

    /**
     * Get arrived.
     *
     * @return \DateTime
     */
    public function getArrived()
    {
        return $this->arrived;
    }

    /**
     * Set departed.
     *
     * @param \DateTime|null $departed
     *
     * @return AreaHistory
     */
    public function setDeparted($departed = null)
    {
        $this->departed = $departed;

        return $this;
    }

    /**
     * Get departed.
     *
     * @return \DateTime|null
     */
    public function getDeparted()
    {
        return $this->departed;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceData()
    {
        if ($this->getVehicle() && $this->getVehicle()->getDevice()) {
            return $this->getVehicle()->getDevice()->toArray();
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getAreaId()
    {
        return $this->getArea()->getId();
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}
