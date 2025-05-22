<?php


namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * DrivingBehavior
 */
#[ORM\Table(name: 'driving_behavior')]
#[ORM\Index(name: 'driving_behavior_vehicle_id_ts_index', columns: ['vehicle_id', 'ts'])]
#[ORM\Index(name: 'driving_behavior_driver_id_ts_index', columns: ['driver_id', 'ts'])]
#[ORM\Index(name: 'driving_behavior_device_id_ts_index', columns: ['device_id', 'ts'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DrivingBehaviorRepository')]
class DrivingBehavior
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var TrackerHistory|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $trackerHistory;

    /**
     * @var Device|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'Device', inversedBy: 'trackerDrivingBehaviorRecords')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', nullable: true)]
    private $driver;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'ts', type: 'datetime')]
    private $ts;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'speed', type: 'float', nullable: true)]
    private $speed;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'odometer', type: 'float', nullable: true)]
    private $odometer;

    /**
     * @var integer|null
     */
    #[ORM\Column(name: 'harsh_acceleration', type: 'smallint', nullable: true)]
    private $harshAcceleration;

    /**
     * @var integer|null
     */
    #[ORM\Column(name: 'harsh_braking', type: 'smallint', nullable: true)]
    private $harshBraking;

    /**
     * @var integer|null
     */
    #[ORM\Column(name: 'harsh_cornering', type: 'smallint', nullable: true)]
    private $harshCornering;

    /**
     * @var string
     */
    #[ORM\Column(name: 'lng', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lng;

    /**
     * @var string
     */
    #[ORM\Column(name: 'lat', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lat;

    /**
     * @var integer|null
     */
    #[ORM\Column(name: 'type_id', type: 'integer', nullable: true)]
    private $typeId;

    /**
     * @var TrackerPayload|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerPayload', inversedBy: 'drivingBehavior')]
    #[ORM\JoinColumn(name: 'tracker_payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerPayload;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setTrackerHistory($trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
    }

    /**
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @param Device|null $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): Vehicle
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
     * @return User
     */
    public function getDriver(): User
    {
        return $this->driver;
    }

    /**
     * @param User|null $driver
     */
    public function setDriver(?User $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return \DateTime
     */
    public function getTs(): \DateTime
    {
        return $this->ts;
    }

    /**
     * @param \DateTime $ts
     */
    public function setTs(\DateTime $ts): void
    {
        $this->ts = $ts;
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
     * @return float|null
     */
    public function getOdometer(): ?float
    {
        return $this->odometer;
    }

    /**
     * @param float|null $odometer
     */
    public function setOdometer(?float $odometer): void
    {
        $this->odometer = $odometer;
    }

    /**
     * @return int|null
     */
    public function getHarshAcceleration(): ?int
    {
        return $this->harshAcceleration;
    }

    /**
     * @param int|null $harshAcceleration
     */
    public function setHarshAcceleration(?int $harshAcceleration): void
    {
        $this->harshAcceleration = $harshAcceleration;
    }

    /**
     * @return int|null
     */
    public function getHarshBraking(): ?int
    {
        return $this->harshBraking;
    }

    /**
     * @param int|null $harshBraking
     */
    public function setHarshBraking(?int $harshBraking): void
    {
        $this->harshBraking = $harshBraking;
    }

    /**
     * @return int|null
     */
    public function getHarshCornering(): ?int
    {
        return $this->harshCornering;
    }

    /**
     * @param int|null $harshCornering
     */
    public function setHarshCornering(?int $harshCornering): void
    {
        $this->harshCornering = $harshCornering;
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->lng;
    }

    /**
     * @param string|null $lng
     */
    public function setLng(?string $lng): void
    {
        $this->lng = $lng;
    }

    /**
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->lat;
    }

    /**
     * @param string|null $lat
     */
    public function setLat(?string $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        return [];
    }

    /**
     * @param $params
     * @param array $include
     * @return array
     */
    public static function toExport($params, array $include = [])
    {
        $data = [];

        if (in_array('totalScore', $include, true)) {
            $data['totalScore'] = $params['totalScore'];
        }

        if (in_array('ecoSpeed', $include, true)) {
            $data['ecoSpeed'] = self::formatScoreAndEventCount($params['ecoSpeed'], $params['ecoSpeedEventCount']);
        }

        if (in_array('ecoSpeedScore', $include, true)) {
            $data['ecoSpeedScore'] = $params['ecoSpeed'];
        }

        if (in_array('excessiveIdling', $include, true)) {
            $data['excessiveIdling'] = self::formatScoreAndEventCount(
                $params['excessiveIdling'],
                $params['idlingCount']
            );
        }

        if (in_array('excessiveIdlingScore', $include, true)) {
            $data['excessiveIdling'] = $params['excessiveIdling'];
        }

        if (in_array('idlingCount', $include, true)) {
            $data['idlingCount'] = $params['idlingCount'];
        }

        if (in_array('harshBraking', $include, true)) {
            $data['harshBraking'] = self::formatScoreAndEventCount(
                $params['harshBrakingScore'],
                $params['harshBrakingCount']
            );
        }

        if (in_array('harshAcceleration', $include, true)) {
            $data['harshAcceleration'] = self::formatScoreAndEventCount(
                $params['harshAccelerationScore'],
                $params['harshAccelerationCount']
            );
        }

        if (in_array('harshAccelerationScore', $include, true)) {
            $data['harshAccelerationScore'] = $params['harshAccelerationScore'];
        }

        if (in_array('harshAccelerationCount', $include, true)) {
            $data['harshAccelerationCount'] = $params['harshAccelerationCount'];
        }

        if (in_array('harshCornering', $include, true)) {
            $data['harshCornering'] = self::formatScoreAndEventCount(
                $params['harshCorneringScore'],
                $params['harshCorneringCount']
            );
        }

        if (in_array('harshCorneringScore', $include, true)) {
            $data['harshCorneringScore'] = $params['harshCorneringScore'];
        }

        if (in_array('harshCorneringCount', $include, true)) {
            $data['harshCorneringCount'] = $params['harshCorneringCount'];
        }

        if (in_array('harshBrakingScore', $include, true)) {
            $data['harshBrakingScore'] = $params['harshBrakingScore'];
        }

        if (in_array('harshBrakingCount', $include, true)) {
            $data['harshBrakingCount'] = $params['harshBrakingCount'];
        }

        if (in_array('totalDistance', $include, true)) {
            $data['totalDistance'] = MetricHelper::metersToKm($params['totalDistance']);
        }

        if (in_array('idlingTotalTime', $include, true)) {
            $data['idlingTotalTime'] = DateHelper::seconds2period($params['idlingTotalTime']);
        }

        if (in_array('drivingTotalTime', $include, true)) {
            $data['drivingTotalTime'] = DateHelper::seconds2period($params['drivingTotalTime']);
        }

        if (in_array('totalAvgSpeed', $include, true)) {
            $data['totalAvgSpeed'] = round($params['totalAvgSpeed'], 3);
        }

        if (in_array('ecoSpeedEventCount', $include, true)) {
            $data['ecoSpeedEventCount'] = $params['ecoSpeedEventCount'];
        }

        if (in_array('ecoSpeedTotalDistance', $include, true)) {
            $data['ecoSpeedTotalDistance'] = $params['ecoSpeedTotalDistance'];
        }

        if (in_array('speeding', $include, true)) {
            $data['speeding'] = $params['speeding'];
        }

        return $data;
    }

    /**
     * @param float $score
     * @param int $eventCount
     * @return string
     */
    private static function formatScoreAndEventCount(float $score, int $eventCount)
    {
        return $score . ' (x' . $eventCount . ')';
    }

    /**
     * @param $params
     * @param array $include
     * @return array
     */
    public static function toExportDriverSummary($params, array $include = [])
    {
        $data = [];

        if (in_array('driver', $include, true)) {
            $data['driver'] = null;

            if ($params['driver']) {
                $data['driver'] = trim($params['driver']['name'] . ' ' . $params['driver']['surname']);
            }
        }

        if (in_array('vehicles', $include, true)) {
            $data['vehicles'] = null;

            if ($params['vehicles']) {
                $data['vehicles'] = implode(', ', array_column($params['vehicles'], 'regNo'));
            }
        }

        return array_merge($data, self::toExport($params, $include));
    }

    /**
     * @param $params
     * @param array $include
     * @return array
     */
    public static function toExportVehicleSummary($params, array $include = [])
    {
        $data = [];

        if (in_array('drivers', $include, true)) {
            $data['drivers'] = null;

            if ($params['drivers']) {
                $data['drivers'] = implode(', ', array_column($params['drivers'], 'fullName'));
            }
        }

        if (in_array('vehicle', $include, true) || in_array('regNo', $include, true)) {
            $data['vehicle'] = null;

            if ($params['vehicle']) {
                $data['vehicle'] = $params['vehicle']['regNo'];
            }
        }

        if (in_array('depot', $include, true)) {
            $data['depot'] = null;

            if ($params['vehicle']['depot']) {
                $data['depot'] = $params['vehicle']['depot']['name'];
            }
        }

        if (in_array('groups', $include, true)) {
            $data['groups'] = null;

            if ($params['vehicle'] && $params['vehicle']['groupsList']) {
                $data['groups'] = $params['vehicle']['groupsList'];
            }
        }

        return array_merge($data, self::toExport($params, $include));
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return self
     */
    public function fromTrackerHistory(TrackerHistory $trackerHistory): self
    {
        $device = $trackerHistory->getDevice();
        $vehicle = $device ? $device->getVehicle() : null;
        $driver = $vehicle ? $vehicle->getDriver() : null;

        $this->setDevice($device);
        $this->setVehicle($vehicle);
        $this->setDriver($driver);
        $this->setTrackerHistory($trackerHistory);
        $this->setTs($trackerHistory->getTs());
        $this->setSpeed($trackerHistory->getSpeed());
        $this->setOdometer($trackerHistory->getOdometer());
        $this->setLng($trackerHistory->getLng());
        $this->setLat($trackerHistory->getLat());
        $this->setTrackerPayload($trackerHistory->getTrackerPayload());

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    /**
     * @param int|null $typeId
     */
    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    /**
     * @return TrackerPayload|null
     */
    public function getTrackerPayload(): ?TrackerPayload
    {
        return $this->getTrackerHistory() ? $this->getTrackerHistory()->getTrackerPayload() : $this->trackerPayload;
    }

    /**
     * @param TrackerPayload|null $trackerPayload
     */
    public function setTrackerPayload(?TrackerPayload $trackerPayload): void
    {
        $this->trackerPayload = $trackerPayload;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->getTrackerHistory()
            ? $this->getTrackerHistory()->getCreatedAt()
            : ($this->getTrackerPayload() ? $this->getTrackerPayload()->getCreatedAt() : null);
    }
}