<?php

namespace App\Entity;

use App\Entity\FuelType\FuelType;
use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Service\File\LocalFileService;
use App\Service\Vehicle\VehicleService;
use App\Util\AttributesTrait;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Vehicle
 */
#[ORM\Table(name: 'vehicle')]
#[ORM\UniqueConstraint(name: 'vehicle_reg_no_team_uindex', columns: ['regno', 'team_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\VehicleRepository')]
#[ORM\EntityListeners(['App\EventListener\Vehicle\VehicleEntityListener'])]
class Vehicle extends BaseEntity
{
    use AttributesTrait;

    public const STATUS_ONLINE = 'online';
    public const STATUS_OFFLINE = 'offline';
    public const STATUS_UNAVAILABLE = 'unavailable';
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;

    public const ALLOWED_STATUSES = [
        self::STATUS_ONLINE,
        self::STATUS_OFFLINE,
        self::STATUS_UNAVAILABLE,
        self::STATUS_DELETED,
        self::STATUS_ARCHIVE
    ];

    public const LIST_STATUSES = [
        self::STATUS_ONLINE,
        self::STATUS_OFFLINE,
        self::STATUS_UNAVAILABLE
    ];

    public const ACTIVE_STATUSES_LIST = [
        self::STATUS_ONLINE,
        self::STATUS_OFFLINE
    ];

    public const REPORT_STATUSES = [
        self::STATUS_ONLINE,
        self::STATUS_OFFLINE,
        self::STATUS_UNAVAILABLE,
        self::STATUS_ARCHIVE
    ];

    public const CAR_TYPES = ['Car', 'Truck', 'Bus'];

    public const DEFAULT_DISPLAY_VALUES = [
        'teamId',
        'team',
        'depot',
        'driver',
        'deviceId',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'unavailableMessage',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'groups',
        'status',
        'year',
        'fuelTankCapacity',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'picture',
        'areas',
        'todayData',
        'ecoSpeed',
        'excessiveIdling',
        'averageFuel',
        'engineOnTime',
        'averageDailyMileage',
        'mileage',
    ];

    public const LIST_DISPLAY_VALUES = [
        'team',
        'depot',
        'driver',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'unavailableMessage',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'groups',
        'status',
        'year',
        'fuelTankCapacity',
        'createdAt',
        'updatedAt',
        'areas',
        'todayData',
        'ecoSpeed',
        'excessiveIdling',
        'averageFuel',
        'engineOnTime',
        'averageDailyMileage',
        'mileage',
    ];

    public const EDITABLE_FIELDS = [
        'client',
        'depot',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'groups',
        'status',
        'year',
        'fuelTankCapacity',
        'updatedBy',
        'ecoSpeed',
        'excessiveIdling',
        'averageFuel',
        'engineOnTime',
    ];

    public const REMINDER_VALUES = [
        'team',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'unavailableMessage',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'status',
        'year',
        'fuelTankCapacity',
        'createdAt',
        'updatedAt',
        'ecoSpeed',
        'excessiveIdling',
        'averageFuel',
        'engineOnTime',
    ];

    public const DISPLAYED_VALUES = [
        'team',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'unavailableMessage',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'status',
        'year',
        'fuelTankCapacity',
        'createdAt',
        'updatedAt',
        'picture',
        'areas',
        'todayData',
        'ecoSpeed',
        'excessiveIdling',
        'averageFuel',
        'engineOnTime',
        'averageDailyMileage',
    ];

    public const REPORT_VALUES = [
        'team',
        'depot',
        'type',
        'typeId',
        'typeName',
        'make',
        'makeModel',
        'model',
        'unavailableMessage',
        'regNo',
        'defaultLabel',
        'vin',
        'regDate',
        'regCertNo',
        'enginePower',
        'engineCapacity',
        'fuelType',
        'emissionClass',
        'co2Emissions',
        'grossWeight',
        'groups',
        'status',
        'year',
        'fuelTankCapacity',
        'createdAt',
        'updatedAt',
        // @todo add `engineOnTime`?
    ];

    public const REPORT_IO_VALUES = [
        'model',
        'regno',
        'defaultlabel',
        'depotName',
        'groups',
        'driverName',
        'inputLabel',
        'tsOn',
        'positionOn',
        'tsOff',
        'positionOff',
        'startAreasName',
        'finishAreasName',
        'duration',
        'distance'
    ];

    /**
     * Vehicle constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->team = $fields['team'] ?? null;
        $this->depot = $fields['depot'] ?? null;
        $this->type = $fields['type'] ?? null;
        $this->make = $fields['make'] ?? null;
        $this->makeModel = $fields['makeModel'] ?? null;
        $this->unavailableMessage = $fields['unavailableMessage'] ?? null;
        $this->regNo = $fields['regNo'] ?? null;
        $this->defaultLabel = $fields['defaultLabel'] ?? null;
        $this->vin = $fields['vin'] ?? null;
        $this->regDate = $fields['regDate'] ?? null;
        $this->regCertNo = $fields['regCertNo'] ?? null;
        $this->enginePower = $fields['enginePower'] ?? null;
        $this->engineCapacity = $fields['engineCapacity'] ?? null;
        $this->fuelType = $fields['fuelType'] ?? null;
        $this->emissionClass = $fields['emissionClass'] ?? null;
        $this->co2Emissions = $fields['co2Emissions'] ?? null;
        $this->grossWeight = $fields['grossWeight'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_OFFLINE;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->groups = new ArrayCollection();
        $this->year = $fields['year'] ?? null;
        $this->fuelTankCapacity = $fields['fuelTankCapacity'] ?? null;
        $this->driver = $fields['driver'] ?? null;
        $this->ecoSpeed = $fields['ecoSpeed'] ?? null;
        $this->excessiveIdling = $fields['excessiveIdling'] ?? null;
        $this->averageFuel = $fields['averageFuel'] ?? null;
        $this->engineOnTime = $fields['engineOnTime'] ?? null;
        $this->reminders = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->driverHistory = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->areaHistories = new ArrayCollection();
        $this->odometerData = new ArrayCollection();
        $this->routes = new ArrayCollection();
        $this->trackerHistoriesLast = new ArrayCollection();
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

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient() ? $this->getClient()->toArray() : null;
        }
        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeam()->getId();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }
        if (in_array('teamType', $include, true)) {
            $data['teamType'] = $this->getTeam()?->getType();
        }
        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->depot ? $this->getDepotData() : null;
        }
        if (in_array('depotName', $include, true)) {
            $data['depotName'] = $this->getDepot() ? $this->getDepot()->getName() : null;
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType()?->convertToOldType();
        }
        if (in_array('typeId', $include, true)) {
            $data['typeId'] = $this->getType()?->getId();
        }
        if (in_array('typeName', $include, true)) {
            $data['typeName'] = $this->getType()?->getName();
        }
        if (in_array('model', $include, true)) {
            $data['model'] = $this->getModel();
        }
        if (in_array('makeModel', $include, true)) {
            $data['makeModel'] = $this->getMakeModel();
        }
        if (in_array('make', $include, true)) {
            $data['make'] = $this->getMake();
        }
        if (in_array('unavailableMessage', $include, true)) {
            $data['unavailableMessage'] = $this->unavailableMessage;
        }
        if (in_array('regNo', $include, true)) {
            $data['regNo'] = $this->regNo;
        }
        if (in_array('defaultLabel', $include, true)) {
            $data['defaultLabel'] = $this->defaultLabel;
        }
        if (in_array('vin', $include, true)) {
            $data['vin'] = $this->vin;
        }
        if (in_array('regDate', $include, true)) {
            $data['regDate'] = $this->formatDate($this->regDate);
        }
        if (in_array('regCertNo', $include, true)) {
            $data['regCertNo'] = $this->regCertNo;
        }
        if (in_array('enginePower', $include, true)) {
            $data['enginePower'] = $this->getEnginePower();
        }
        if (in_array('engineCapacity', $include, true)) {
            $data['engineCapacity'] = $this->getEngineCapacity();
        }
        if (in_array('fuelType', $include, true)) {
            $data['fuelType'] = $this->getFuelType()?->getId();
        }
        if (in_array('fuelTypeArray', $include, true)) {
            $data['fuelTypeArray'] = $this->getFuelTypeArray();
        }
        if (in_array('emissionClass', $include, true)) {
            $data['emissionClass'] = $this->emissionClass;
        }
        if (in_array('co2Emissions', $include, true)) {
            $data['co2Emissions'] = $this->getCo2Emissions();
        }
        if (in_array('grossWeight', $include, true)) {
            $data['grossWeight'] = $this->getGrossWeight();
        }
        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getGroupsArray();
        }
        if (in_array('groupsList', $include, true)) {
            $data['groupsList'] = $this->getGroupsString();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->status;
        }
        if (in_array('year', $include, true)) {
            $data['year'] = $this->year;
        }
        if (in_array('fuelTankCapacity', $include, true)) {
            $data['fuelTankCapacity'] = $this->fuelTankCapacity;
        }
        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriverData();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedByData();
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()?->toArray(array_diff(
                Device::DEFAULT_DISPLAY_VALUES,
                ['deviceInstallation', 'updatedBy', 'createdBy', 'team']
            ));
        }

        $data = $this->getNestedFields('device', $include, $data);

        if (in_array('deviceDriverData', $include, true)) {
            $data['deviceDriverData'] = $this->getDeviceDriverData();
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('picture', $include, true)) {
            $data['picture'] = $this->getPicturePath();
        }
        if (in_array('areas', $include, true)) {
            $data['areas'] = $this->currentAreasData();
        }
        if (in_array('todayData', $include, true)) {
            $data['todayData'] = $this->getTodayData();
        }
        if (in_array('ecoSpeed', $include, true)) {
            $data['ecoSpeed'] = $this->getEcoSpeed();
        }
        if (in_array('excessiveIdling', $include, true)) {
            $data['excessiveIdling'] = $this->getExcessiveIdling();
        }
        if (in_array('reminders', $include, true)) {
            if (isset($include['serviceStartDate']) && isset($include['serviceEndDate'])) {
                $serviceStartDate = new Carbon($include['serviceStartDate']);
                $serviceEndDate = new Carbon($include['serviceEndDate']);
                $data['reminders'] = $this->getRemindersArray($serviceStartDate, $serviceEndDate);
            } else {
                $data['reminders'] = $this->getRemindersArray();
            }
        }
        if (in_array('driverHistory', $include, true)) {
            $data['driverHistory'] = $this->getCurrentDriverHistory();
        }
        if (in_array('averageFuel', $include, true)) {
            $data['averageFuel'] = $this->getAverageFuel();
        }
        if (in_array('engineOnTime', $include, true)) {
            $data['engineOnTime'] = $this->getEngineOnTime();
        }
        if (in_array('averageDailyMileage', $include, true)) {
            $data['averageDailyMileage'] = $this->getAverageDailyMileage();
        }
        // @todo
        if ((!$this->getDevice() || ($this->getDevice() && !$this->getDevice()->getLastTrackerRecord()))
            && in_array('mileage', $include, true)
        ) {
            $data['mileage'] = $this->getLastOdometerValue($this->getLastTrackerRecordOdometer());
        }
        if (in_array('lastStatusDuration', $include, true)) {
            $data['lastStatusDuration'] = $this->getLastStatusDuration();
        }
        if (in_array('lastRoute', $include, true)) {
            if ($lastRoute = $this->getLastRoute()) {
                $includeForRoute = $this->getNestedIncludeByPrefix('lastRoute', $include, $data);
                $this->vehicleService->addCoordinatesToRoute($lastRoute, $includeForRoute);
                $lastRoute = $lastRoute->toArray(array_merge(Route::DEFAULT_DISPLAY_VALUES, $includeForRoute));
            }

            $data['lastRoute'] = $lastRoute;
        }
        if (in_array('assets', $include, true)) {
            $vehicleAssets = $this->getVehicleAssets();

            $data['assets'] = array_map(function (Asset $asset) {
                return $asset->toArray();
            }, $vehicleAssets);
        }

        return $data;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toExport(array $include = []): array
    {
        $data = [];

        if (in_array('regNo', $include, true)) {
            $data['regNo'] = $this->getRegNo();
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->type ? $this->getType()->getName() : null;
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('model', $include, true)) {
            $data['model'] = $this->getModel();
        }

        if (in_array('driverName', $include, true)) {
            $data['driverName'] = $this->getDriverName();
        }

        if (in_array('makeModel', $include, true)) {
            $data['makeModel'] = $this->getMakeModel();
        }

        if (in_array('make', $include, true)) {
            $data['make'] = $this->getMake();
        }

        if (in_array('defaultLabel', $include, true)) {
            $data['defaultLabel'] = $this->getDefaultLabel();
        }

        if (in_array('year', $include, true)) {
            $data['year'] = $this->getYear();
        }

        if (in_array('regDate', $include, true)) {
            $data['regDate'] = $this->formatDate($this->regDate, self::EXPORT_DATE_FORMAT);
        }

        if (in_array('vin', $include, true)) {
            $data['vin'] = $this->getVin();
        }

        if (in_array('deviceImei', $include, true)) {
            $data['deviceImei'] = $this->getDevice()?->getImei();
        }

        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->depot ? $this->getDepot()->getName() : null;
        }

        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getGroupsString();
        }

        if (in_array('mileage', $include, true)) {
            $data['mileage'] = MetricHelper::metersToHumanKm(
                $this->getLastOdometerValue($this->getLastTrackerRecordOdometer())
            );
        }

        if (in_array('engineHours', $include, true)) {
            if (in_array('toExport', $include, true)) {
                $data['engineHours'] = DateHelper::toHours($this->getEngineOnTime());
            } else {
                $data['engineHours'] = DateHelper::seconds2human($this->getEngineHours());
            }
              
        }

        if (in_array('fuelType', $include, true)) {
            $data['fuelType'] = $this->getFuelType() ? $this->getFuelType()->getName() : null;
        }

        if (in_array('fuelTankCapacity', $include, true)) {
            $data['fuelTankCapacity'] = $this->fuelTankCapacity;
        }

        if (in_array('ecoSpeed', $include, true)) {
            $data['ecoSpeed'] = $this->getEcoSpeed();
        }

        if (in_array('excessiveIdling', $include, true)) {
            $data['excessiveIdling'] = $this->getExcessiveIdling();
        }

        if (in_array('averageDailyMileage', $include, true)) {
            $data['averageDailyMileage'] = $this->getAverageDailyMileage();
        }

        // @todo add `engineOnTime`?

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
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'vehicles')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var User
     */
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', nullable: true)]
    private $driver;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Depot', inversedBy: 'vehicles')]
    #[ORM\JoinColumn(name: 'depot_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $depot;

    /**
     * @var VehicleType
     */
    #[ORM\ManyToOne(targetEntity: 'VehicleType', inversedBy: 'vehicles')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $type;

    /**
     * @var int
     */
    #[ORM\Column(name: 'make', type: 'string', nullable: true)]
    private $make;

    /**
     * @var int
     */
    #[ORM\Column(name: 'make_model', type: 'string', nullable: true)]
    private $makeModel;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'unavailable_message', type: 'text', nullable: true)]
    private $unavailableMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'regNo', type: 'string', length: 255, nullable: true)]
    private $regNo;

    /**
     * @var string
     */
    #[ORM\Column(name: 'defaultLabel', type: 'string', length: 255, nullable: true)]
    private $defaultLabel;

    /**
     * @var string
     */
    #[ORM\Column(name: 'vin', type: 'string', length: 255, nullable: true)]
    private $vin;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'regDate', type: 'datetime', nullable: true)]
    private $regDate;

    /**
     * @var string
     */
    #[ORM\Column(name: 'regCertNo', type: 'string', length: 255, nullable: true)]
    private $regCertNo;

    /**
     * @var string
     */
    #[ORM\Column(name: 'enginePower', type: 'float', nullable: true)]
    private $enginePower;

    /**
     * @var string
     */
    #[ORM\Column(name: 'engineCapacity', type: 'float', nullable: true)]
    private $engineCapacity;

    /**
     * @var FuelType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\FuelType\FuelType')]
    #[ORM\JoinColumn(name: 'fuel_type_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $fuelType;

    /**
     * @var string
     */
    #[ORM\Column(name: 'emissionClass', type: 'string', length: 50, nullable: true)]
    private $emissionClass;

    /**
     * @var float
     */
    #[ORM\Column(name: 'co2Emissions', type: 'float', nullable: true)]
    private $co2Emissions;

    /**
     * @var float
     */
    #[ORM\Column(name: 'grossWeight', type: 'float', nullable: true)]
    private $grossWeight;

    /**
     * Many Vehicles have Many Groups.
     */
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
    #[ORM\ManyToMany(targetEntity: 'VehicleGroup', mappedBy: 'vehicles', fetch: 'EXTRA_LAZY')]
    private $groups;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100, nullable: true)]
    private $status;

    /**
     * @var int
     */
    #[ORM\Column(name: 'year', type: 'integer', nullable: true)]
    private $year;

    /**
     * @var float
     */
    #[ORM\Column(name: 'fuelTankCapacity', type: 'float', nullable: true)]
    private $fuelTankCapacity;

    /**
     * @var Device
     */
    #[ORM\OneToOne(targetEntity: 'Device')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $device;

    /**
     * @var int
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'picture_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $picture;

    private $todayData;

    /**
     * @var ArrayCollection|Reminder[]
     */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: 'Reminder', fetch: 'EXTRA_LAZY')]
    private $reminders;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: 'Document', fetch: 'EXTRA_LAZY')]
    private $documents;

    /**
     * @var int
     */
    #[ORM\Column(name: 'ecoSpeed', type: 'integer', nullable: true)]
    private $ecoSpeed;

    /**
     * @var int
     */
    #[ORM\Column(name: 'excessiveIdling', type: 'integer', nullable: true)]
    private $excessiveIdling;

    /**
     * @var float
     */
    #[ORM\Column(name: 'average_fuel', type: 'float', nullable: true)]
    private $averageFuel;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'engine_on_time', type: 'bigint', nullable: true)]
    private $engineOnTime;

    /**
     * @var int
     */
    #[ORM\OneToMany(targetEntity: 'Note', mappedBy: 'vehicle')]
    private $notes;

    /**
     * @var ArrayCollection|DriverHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'DriverHistory', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $driverHistory;

    /**
     * Many Vehicles have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'vehicles', fetch: 'EXTRA_LAZY')]
    private $userGroups;

    /**
     * @var ArrayCollection|Reminder[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\AreaHistory', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $areaHistories;

    private ?VehicleService $vehicleService;

    private $averageDailyMileage;

    /**
     * @var ArrayCollection|VehicleOdometer[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\VehicleOdometer', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $odometerData;

    /**
     * @var ArrayCollection|VehicleOdometer[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\VehicleEngineHours', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $engineHoursData;

    /**
     * @var ArrayCollection|TrackerHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistory', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $trackerRecords;

    /**
     * @var ArrayCollection|TrackerHistorySensor[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $trackerSensorHistories;

    /**
     * @var ArrayCollection|Route[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Route', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $routes;

    /**
     * @var ArrayCollection|TrackerHistoryLast[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistoryLast', mappedBy: 'vehicle', fetch: 'EXTRA_LAZY')]
    private $trackerHistoriesLast;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return Vehicle
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDriverName(): ?string
    {
        return $this->driver ? $this->driver->getFullName() : null;
    }

    /**
     * @return string|null
     */
    public function getDriverEmail(): ?string
    {
        return $this->driver ? $this->driver->getEmail() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getDriverData(): ?array
    {
        return $this->driver ? $this->driver->toArray(User::SIMPLE_VALUES) : null;
    }

    /**
     * @return User|null
     */
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->getDriver()?->getId();
    }

    /**
     * @param User $driver
     * @return $this
     */
    public function setDriver(?User $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get team
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient() : null;
    }

    /**
     * Get clientName
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient()->getName() : null;
    }

    /**
     * Get client
     *
     * @return int|null
     */
    public function getClientId(): ?int
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClientId() : null;
    }

    /**
     * Set depot
     *
     * @param Depot $depot
     *
     * @return Vehicle
     */
    public function setDepot(?Depot $depot)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return Depot
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * @return array|null
     */
    public function getDepotData()
    {
        return $this->depot ? $this->getDepot()->toArray(['name', 'status', 'createdAt', 'color']) : null;
    }

    /**
     * Set type
     *
     * @param VehicleType $type
     *
     * @return Vehicle
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return VehicleType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()  // return virtual field as sql function for vehicle table 'select vehicle.model ....'
    {
        if ($this->getMake()) {
            return $this->getMake() . ' ' . $this->getMakeModel();
        } else {
            return $this->getMakeModel();
        }
    }

    /**
     * Set make
     *
     * @param string $make
     *
     * @return Vehicle
     */
    public function setMake(string $make)
    {
        $this->make = $make;

        return $this;
    }

    /**
     * Get make
     *
     * @return string
     */
    public function getMake()
    {
        return !empty($this->make) ? $this->make : null;
    }

    /**
     * Set makeModel
     *
     * @param string $makeModel
     *
     * @return Vehicle
     */
    public function setMakeModel(string $makeModel)
    {
        $this->makeModel = $makeModel;

        return $this;
    }

    /**
     * Get makeModel
     *
     * @return string
     */
    public function getMakeModel()
    {
        return !empty($this->makeModel) ? $this->makeModel : null;
    }

    /**
     * Set unavailableMessage
     *
     * @param boolean $unavailableMessage
     *
     * @return Vehicle
     */
    public function setUnavailableMessage($unavailableMessage)
    {
        $this->unavailableMessage = $unavailableMessage;

        return $this;
    }

    /**
     * Get unavailableMessage
     *
     * @return bool
     */
    public function getUnavailableMessage()
    {
        return $this->unavailableMessage;
    }

    /**
     * Set regNo
     *
     * @param string $regNo
     *
     * @return Vehicle
     */
    public function setRegNo($regNo)
    {
        $this->regNo = $regNo;

        return $this;
    }

    /**
     * Get regNo
     *
     * @return string
     */
    public function getRegNo()
    {
        return $this->regNo;
    }

    /**
     * Set defaultLabel
     *
     * @param string $defaultLabel
     *
     * @return Vehicle
     */
    public function setDefaultLabel($defaultLabel)
    {
        $this->defaultLabel = $defaultLabel;

        return $this;
    }

    /**
     * Get defaultLabel
     *
     * @return string
     */
    public function getDefaultLabel()
    {
        return $this->defaultLabel;
    }

    /**
     * Set vin
     *
     * @param string $vin
     *
     * @return Vehicle
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }

    /**
     * Get vin
     *
     * @return string
     */
    public function getVin()
    {
        return !empty($this->vin) ? $this->vin : null;
    }

    /**
     * Set regDate
     *
     * @param \DateTime $regDate
     *
     * @return Vehicle
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;

        return $this;
    }

    /**
     * Get regDate
     *
     * @return \DateTime
     */
    public function getRegDate()
    {
        return $this->regDate;
    }

    /**
     * Set regCertNo
     *
     * @param string $regCertNo
     *
     * @return Vehicle
     */
    public function setRegCertNo($regCertNo)
    {
        $this->regCertNo = $regCertNo;

        return $this;
    }

    /**
     * Get regCertNo
     *
     * @return string
     */
    public function getRegCertNo()
    {
        return $this->regCertNo;
    }

    /**
     * Set enginePower
     *
     * @param string $enginePower
     *
     * @return Vehicle
     */
    public function setEnginePower($enginePower)
    {
        $this->enginePower = $enginePower;

        return $this;
    }

    /**
     * Get enginePower
     *
     * @return string
     */
    public function getEnginePower()
    {
        return (float)$this->enginePower;
    }

    /**
     * Set engineCapacity
     *
     * @param string $engineCapacity
     *
     * @return Vehicle
     */
    public function setEngineCapacity($engineCapacity)
    {
        $this->engineCapacity = $engineCapacity;

        return $this;
    }

    /**
     * Get engineCapacity
     *
     * @return string
     */
    public function getEngineCapacity()
    {
        return (float)$this->engineCapacity;
    }

    /**
     * Set fuelType
     *
     * @param FuelType|null $fuelType
     * @return $this
     */
    public function setFuelType(?FuelType $fuelType)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * Get fuelType
     *
     * @return FuelType|null
     */
    public function getFuelType(): ?FuelType
    {
        return $this->fuelType;
    }


    /**
     * @return array
     */
    public function getFuelTypeArray()
    {
        return $this->fuelType ? $this->fuelType->toArray(['id', 'name']) : null;
    }

    /**
     * Set emissionClass
     *
     * @param string $emissionClass
     *
     * @return Vehicle
     */
    public function setEmissionClass($emissionClass)
    {
        $this->emissionClass = $emissionClass;

        return $this;
    }

    /**
     * Get emissionClass
     *
     * @return string
     */
    public function getEmissionClass()
    {
        return $this->emissionClass;
    }

    /**
     * Set co2Emissions
     *
     * @param float $co2Emissions
     *
     * @return Vehicle
     */
    public function setCo2Emissions($co2Emissions)
    {
        $this->co2Emissions = $co2Emissions;

        return $this;
    }

    /**
     * Get co2Emissions
     *
     * @return float
     */
    public function getCo2Emissions()
    {
        return (float)$this->co2Emissions;
    }

    /**
     * Set grossWeight
     *
     * @param float $grossWeight
     *
     * @return Vehicle
     */
    public function setGrossWeight($grossWeight)
    {
        $this->grossWeight = $grossWeight;

        return $this;
    }

    /**
     * Get grossWeight
     *
     * @return float
     */
    public function getGrossWeight()
    {
        return (float)$this->grossWeight;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getGroupsArray(): array
    {
        return $this->getGroups()->map(
            static function (VehicleGroup $g) {
                return $g->toArray(['name', 'color']);
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getGroupsString()
    {
        $groups = array_map(
            function ($group) {
                return $group->getName();
            },
            $this->groups->toArray()
        );

        return implode(",", $groups);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function addToGroup(VehicleGroup $vehicleGroup)
    {
        $this->groups->add($vehicleGroup);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function removeFromGroup(VehicleGroup $vehicleGroup)
    {
        $this->groups->removeElement($vehicleGroup);
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Vehicle
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Vehicle
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getCreatedByName(): ?string
    {
        return $this->getCreatedBy()?->getFullName();
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Vehicle
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return Vehicle
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return string|null
     */
    public function getUpdatedByName()
    {
        return $this->updatedBy ? $this->updatedBy->getFullName() : null;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Vehicle
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Vehicle
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set fuelTankCapacity
     *
     * @param float $fuelTankCapacity
     *
     * @return Vehicle
     */
    public function setFuelTankCapacity($fuelTankCapacity)
    {
        $this->fuelTankCapacity = $fuelTankCapacity;

        return $this;
    }

    /**
     * Get fuelTankCapacity
     *
     * @return float
     */
    public function getFuelTankCapacity()
    {
        return $this->fuelTankCapacity ? (float)$this->fuelTankCapacity : null;
    }

    /**
     * @return array|null
     */
    public function getUpdatedByData(): ?array
    {
        return $this->updatedBy ? $this->updatedBy->toArray(['id', 'fullName', 'teamType', 'email']) : null;
    }

    /**
     * @return array|null
     */
    public function getCreatedByData(): ?array
    {
        return $this->createdBy ? $this->createdBy->toArray(['id', 'fullName', 'teamType', 'email']) : null;
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
        return $this->device ? $this->device->getId() : null;
    }

    /**
     * @return mixed|null
     */
    public function getMileage()
    {
        return $this->device ? $this->device->getMileage() : null;
    }

    /**
     * @return mixed|null
     */
    public function getEngineHours()
    {
        return $this->device ? $this->device->getEngineHours() : null;
    }


    /**
     * @param Device $device
     * @return Vehicle
     */
    public function setDevice(?Device $device): Vehicle
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Set picture
     *
     * @param File $picture
     *
     * @return Vehicle
     */
    public function setPicture(File $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return int
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get picture path
     *
     * @return string
     */
    public function getPicturePath()
    {
        return $this->picture ? LocalFileService::VEHICLE_PUBLIC_PATH . $this->picture->getName() : null;
    }

    /**
     * @return bool
     */
    public function isUnavailable()
    {
        return $this->getStatus() === self::STATUS_UNAVAILABLE;
    }

    public function makeUnavailable()
    {
        $this->setStatus(self::STATUS_UNAVAILABLE);
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceDriverData()
    {
        return $this->getDevice()?->toArray(array_merge(Device::SIMPLE_FIELDS, ['trackerData', 'lastRoute']));
    }

    /**
     * @param AreaHistory $areaHistory
     * @return $this
     */
    public function addAreaHistory(AreaHistory $areaHistory)
    {
        $this->areaHistories->add($areaHistory);

        return $this;
    }

    /**
     * @param AreaHistory $areaHistory
     * @return $this
     */
    public function removeAreaHistory(AreaHistory $areaHistory)
    {
        if ($this->getCurrentAreaHistories()) {
            $this->areaHistories->removeElement($areaHistory);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isInArea()
    {
        if ($this->getCurrentAreaHistories()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAreas()
    {
        if ($this->getCurrentAreaHistories()) {
            return array_map(
                function ($areaHistory) {
                    return $areaHistory->getArea();
                },
                $this->getCurrentAreaHistories()
            );
        } else {
            return [];
        }
    }

    /**
     * @return array
     */
    public function getCurrentAreaHistories()
    {
        return $this->areaHistories->matching(
            Criteria::create()
                ->where(Criteria::expr()->isNull('departed'))
        )->toArray();
    }

    /**
     * @return array
     */
    public function currentAreasData()
    {
        $data = [];

        foreach ($this->getCurrentAreaHistories() as $currentArea) {
            if (!isset($data[$currentArea->getAreaId()])) {
                $data[$currentArea->getAreaId()] = $currentArea->toArray(AreaHistory::VEHICLE_DISPLAY_VALUES);
            }
        }

        return array_values($data);
    }

    /**
     * @return array
     */
    public function getAreasGroups()
    {
        $groups = [];
        if ($this->getCurrentAreaHistories()) {
            foreach ($this->getCurrentAreaHistories() as $areaHistory) {
                $groups = array_merge($groups, $areaHistory->getArea()->getGroups()->toArray());
            }
        }

        return $groups;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setTodayData(array $data)
    {
        $this->todayData = $data;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTodayData(): ?array
    {
        return $this->getVehicleService()->getDailyData($this);
    }

    /**
     * @param $speed
     * @return $this
     */
    public function setEcoSpeed($speed)
    {
        $this->ecoSpeed = $speed;

        return $this;
    }

    /**
     * @return int
     */
    public function getEcoSpeed()
    {
        return (int)$this->ecoSpeed;
    }

    /**
     * @param $idle
     * @return $this
     */
    public function setExcessiveIdling($idle)
    {
        $this->excessiveIdling = $idle;

        return $this;
    }

    /**
     * @return int
     */
    public function getExcessiveIdling()
    {
        return (int)$this->excessiveIdling;
    }

    public function getReminders()
    {
        return array_filter(
            $this->reminders->toArray(),
            function (Reminder $reminder) {
                return $reminder->getStatus() !== Reminder::STATUS_DELETED;
            }
        );
    }

    /**
     * @return bool
     */
    public function hasReminders()
    {
        return (bool)$this->reminders->count();
    }

    /**
     * @return array
     */
    public function getRemindersCategories()
    {
        $categories = [];
        foreach ($this->reminders as $reminder) {
            if ($reminder->getCategory()) {
                $categories[] = $reminder->getCategory();
            }
        }

        return $categories;
    }

    /**
     * @param \DateTime|null $serviceStartDate
     * @param \DateTime|null $serviceEndDate
     * @return array
     */
    public function getRemindersArray(?\DateTime $serviceStartDate = null, ?\DateTime $serviceEndDate = null)
    {
        $dateFilter = [
            'serviceStartDate' => $serviceStartDate,
            'serviceEndDate' => $serviceEndDate
        ];

        return array_map(
            function (Reminder $reminder) use ($dateFilter) {
                return $reminder->toArray(array_merge(Reminder::VEHICLE_REPORT_DISPLAY_VALUES, $dateFilter));
            },
            array_values($this->getReminders())
        );
    }

    /**
     * @return ArrayCollection|DriverHistory[]
     */
    public function getDriverHistory()
    {
        return $this->driverHistory;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentDriverHistory()
    {
        $currentDriverHistory = $this->driverHistory->matching(
            Criteria::create()
                ->where(Criteria::expr()->isNull('finishDate'))
        );

        if (count($currentDriverHistory) === 1) {
            return $currentDriverHistory->first()->toArray(['startDate']);
        } else {
            return null;
        }
    }

    /**
     * @param $averageFuel
     * @return $this
     */
    public function setAverageFuel($averageFuel)
    {
        $this->averageFuel = $averageFuel;

        return $this;
    }

    /**
     * @return float|mixed|null
     */
    public function getAverageFuel()
    {
        return (float)$this->averageFuel;
    }

    /**
     * @param UserGroup $userGroup
     */
    public function addToUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->add($userGroup);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function removeFromUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }

    /**
     * @param VehicleService $vehicleService
     * @return $this
     */
    public function setVehicleService(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;

        return $this;
    }

    /**
     * @return VehicleService
     */
    public function getVehicleService(): VehicleService
    {
        return $this->vehicleService;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getClient() ? $this->getClient()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return int|null
     */
    public function getEngineOnTime(): ?int
    {
        return $this->engineOnTime;
    }

    /**
     * @param int|null $engineOnTime
     */
    public function setEngineOnTime(?int $engineOnTime): void
    {
        $this->engineOnTime = $engineOnTime;
    }

    /**
     * @param int|null $engineOnTime
     */
    public function increaseEngineOnTime(?int $engineOnTime): void
    {
        $this->setEngineOnTime($this->getEngineOnTime() + $engineOnTime);
    }

    public function decreaseEngineOnTime(?int $engineOnTime): void
    {
        $this->setEngineOnTime($this->getEngineOnTime() - $engineOnTime);
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAverageDailyMileage()
    {
        return $this->getVehicleService()->getAverageDailyMileage($this);
    }

    /**
     * @return TrackerHistory|null
     */
    public function getLastTrackerRecord(): ?TrackerHistory
    {
        if (!$this->trackerRecords) {
            return null;
        }

        return $this->trackerRecords->matching(
            Criteria::create()
                ->orderBy(['ts' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @return float|null
     */
    public function getLastTrackerRecordOdometer(): ?float
    {
        if (!$this->trackerHistoriesLast) {
            return null;
        }

        $result = $this->trackerHistoriesLast->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('odometer', null))
                ->orderBy(['ts' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return $result ? $result->getOdometer() : null;
    }

    public function getLastTrackerRecordWithOdometer(): ?TrackerHistoryLast
    {
        if (!$this->trackerHistoriesLast) {
            return null;
        }

        $result = $this->trackerHistoriesLast->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('odometer', null))
                ->orderBy(['ts' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return $result ?: null;
    }

    /**
     * @return VehicleOdometer[]|ArrayCollection|null
     */
    public function getOdometerData(): ?Collection
    {
        return $this->odometerData;
    }

    /**
     * @param float|null $lastTROdometer
     * @return float|null
     */
    public function getLastOdometerValue(?float $lastTROdometer): ?float
    {
        if (!$this->odometerData) {
            return null;
        }

        $result = $this->odometerData->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('odometer', null))
                ->orderBy(['occurredAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        if ($lastTROdometer) {
            $value = $result ? $lastTROdometer + $result->getAccuracy() : $lastTROdometer;
        } else {
            $value = $result ? $result->getOdometer() + $result->getAccuracy() : null;
        }

        return $value > 0 ? $value : 0;
    }

    /**
     * @return VehicleOdometer|null
     */
    public function getLastOdometerData(): ?VehicleOdometer
    {
        if (!$this->odometerData->count()) {
            return null;
        }

        $result = $this->odometerData->matching(
            Criteria::create()
                ->orderBy(['occurredAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return $result ?: null;
    }

    public function getLastEngineHours(): ?VehicleEngineHours
    {
        if (!$this->engineHoursData) {
            return null;
        }

        $engineHours = $this->engineHoursData->matching(
            Criteria::create()
                ->orderBy(['id' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return $engineHours ?: null;
    }

    public function setStatusByDevice(): Vehicle
    {
        if ($this->getDevice() && in_array(
                $this->getDevice()->getStatus(),
                [Device::STATUS_DRIVING, Device::STATUS_IDLE, Device::STATUS_STOPPED]
            ) && !in_array(
                $this->getStatus(),
                [self::STATUS_DELETED, self::STATUS_UNAVAILABLE]
            )) {
            $this->setStatus(self::STATUS_ONLINE);
        } elseif (!in_array(
            $this->getStatus(),
            [self::STATUS_DELETED, self::STATUS_UNAVAILABLE, self::STATUS_OFFLINE]
        )) {
            $this->setStatus(self::STATUS_OFFLINE);
        }

        return $this;
    }

    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /**
     * @return Route|null
     */
    public function getLastRoute(): ?Route
    {
        if ($this->getRoutes()->isEmpty()) {
            return null;
        }

        return $this->getRoutes()->matching(
            Criteria::create()
                ->orderBy(['startedAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();
    }

    /**
     * @return int|null
     */
    public function getLastStatusDuration(): ?int
    {
        $lastRoute = $this->getLastRoute();

        return ($lastRoute && $lastRoute->getStartedAt())
            ? (new Carbon())->subRealSeconds($lastRoute->getStartedAt()->getTimestamp())->getTimestamp()
            : null;
    }

    /**
     * @return array|null
     */
    public function getVehicleAssets(): array
    {
        $device = $this->getDevice();

        if (!$device) {
            return [];
        }

        return $device->getAssets();
    }

    /**
     * @return bool
     */
    public function isVehicleWithAssets(): bool
    {
        $device = $this->getDevice();

        if (!$device) {
            return false;
        }

        $deviceSensors = $device->getDeviceSensorsWithTypeHavingTemp();

        return $deviceSensors ? true : false;
    }

    /**
     * @return bool
     */
    public function isInAssetList(): bool
    {
        return $this->isVehicleWithAssets();
    }

    public function getLastOdometer()
    {
        return $this->getLastOdometerValue($this->getLastTrackerRecordOdometer());
    }

    public function getRegNoWithModel($addText = null): ?string
    {
        if ($this->getModel()) {
            if ($addText) {
                return vsprintf('%s %s (%s)', [$addText, $this->getRegNo(), $this->getModel()]);
            }

            return vsprintf('%s (%s)', [$this->getRegNo(), $this->getModel()]
            );
        } else {
            if ($addText) {
                return vsprintf('%s %s', [$addText, $this->getRegNo()]);
            }

            return vsprintf('%s', [$this->getRegNo()]);
        }
    }

    public function getDeviceImei(): ?string
    {
        return $this->getDevice()?->getImei();
    }

    public function getEventNameByStatus(): ?string
    {
        if ($this->getStatus() === self::STATUS_ONLINE) {
            return Event::VEHICLE_ONLINE;
        } elseif ($this->getStatus() === self::STATUS_OFFLINE) {
            return Event::VEHICLE_OFFLINE;
        }

        return null;
    }

    public function getFullSearchField(): ?string
    {
        return $this->getDefaultLabel() . ' ' . $this->getRegNo() . ' ' . $this->getDriverName() . ' ' . $this->getVin();
    }

    public function getDocuments()
    {
        return $this->documents;
    }
}
