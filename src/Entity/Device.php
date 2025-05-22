<?php

namespace App\Entity;

use App\Entity\Tracker\TraccarEventHistory;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryDTCVIN;
use App\Entity\Tracker\TrackerHistoryJammer;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Enums\EntityHistoryTypes;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Service\Tracker\Stream\TrackerStreamService;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device
 */
#[ORM\Table(name: 'device')]
#[ORM\Index(columns: ['traccar_device_id'], name: 'device_traccar_device_id_index')]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceRepository')]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners(['App\EventListener\Device\DeviceEntityListener'])]
class Device extends BaseEntity
{
    use AttributesTrait;

    public const STATUS_IN_STOCK = 'inStock';          // in stock, not connected to any vehicle
    public const STATUS_DRIVING = 'driving';           // connected to vehicle, data received, engine on and moving
    public const STATUS_IDLE = 'idle';                 // connected to vehicle, data received, engine on but not moving
    public const STATUS_STOPPED = 'stopped';           // connected to vehicle, data received, engine off
    public const STATUS_TOWING = 'towing';           // connected to vehicle, data received, engine off but moving
    public const STATUS_OFFLINE = 'offline';           // connected to vehicle, data not received past X min
    public const STATUS_UNAVAILABLE = BaseEntity::STATUS_UNAVAILABLE;   // manually checked as unavailable
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;
    public const STATUS_IN_STOCK_ADMIN = 'inStockAdmin';
    public const STATUS_IN_STOCK_RESELLER = 'inStockReseller';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_EXT_OK = 'ok';
    public const STATUS_EXT_NO_GPS = 'noGps';
    public const STATUS_EXT_NO_POWER = 'noPower';
    public const STATUS_EXT_NO_POWER_LIMIT = 5000; // mV
    public const STATUS_EXT_OFFLINE = 'offline';
    public const STATUS_EXT_OFFLINE_LIMIT = 60 * 60 * 24;

    public const ALLOWED_STATUSES = [
        self::STATUS_IN_STOCK,
        self::STATUS_DRIVING,
        self::STATUS_IDLE,
        self::STATUS_STOPPED,
        self::STATUS_OFFLINE,
        self::STATUS_UNAVAILABLE,
        self::STATUS_DELETED,
        self::STATUS_IN_STOCK_ADMIN,
        self::STATUS_IN_STOCK_RESELLER,
        self::STATUS_RETURNED,
    ];

    public const LIST_STATUSES = [
        self::STATUS_IN_STOCK,
        self::STATUS_DRIVING,
        self::STATUS_IDLE,
        self::STATUS_STOPPED,
        self::STATUS_OFFLINE,
//        self::STATUS_UNAVAILABLE,
        self::STATUS_IN_STOCK_ADMIN,
        self::STATUS_IN_STOCK_RESELLER,
        self::STATUS_RETURNED,
    ];

    public const INSTALLER_LIST_STATUSES = [
        self::STATUS_IN_STOCK,
        self::STATUS_UNAVAILABLE,
        self::STATUS_IN_STOCK_ADMIN,
        self::STATUS_IN_STOCK_RESELLER
    ];

    public const ACTIVE_STATUSES_LIST = [
        self::STATUS_DRIVING,
        self::STATUS_IDLE,
        self::STATUS_STOPPED,
        self::STATUS_OFFLINE
    ];

    public const LIST_EXT_STATUSES = [
        self::STATUS_EXT_OK,
        self::STATUS_EXT_NO_GPS,
        self::STATUS_EXT_NO_POWER,
        self::STATUS_EXT_OFFLINE,
    ];

    public const USAGE_VEHICLE = 'vehicle';
    public const USAGE_PERSONAL = 'personal';
    public const USAGE_ASSET = 'asset';
    public const USAGE_SATELLITE = 'satellite';

    public const ALLOWED_USAGE = [
        self::USAGE_VEHICLE,
        self::USAGE_PERSONAL,
        self::USAGE_ASSET,
        self::USAGE_SATELLITE,
    ];

    public const OWNERSHIP_LINXIO = 'linxio';
    public const OWNERSHIP_CLIENT = 'client';

    public const DEFAULT_DISPLAY_VALUES = [
        'vendor',
        'model',
        'team',
        'sn',
        'status',
        'statusExt',
        'port',
        'hw',
        'sw',
        'imei',
        'phone',
        'imsi',
        'iccid',
        'installDate',
        'uninstallDate',
        'deviceInstallation',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'trackerData',
        'blockingMessage',
        'isFixWithSpeed',
        'statusUpdatedAt',
        'lastDataReceivedAt',
        'usage',
        'isDeactivated',
        'isUnavailable',
        'contractFinishAt',
        'contractStartAt',
        'lastActiveTime',
        'contractId',
        'professionalInstall',
        'ownership',
        'hasCameras',
    ];

    public const SIMPLE_FIELDS = [
        'vendor',
        'model',
        'team',
        'sn',
        'status',
        'statusExt',
        'port',
        'hw',
        'sw',
        'imei',
        'phone',
        'imsi',
        'iccid',
        'createdAt',
        'updatedAt',
        'blockingMessage',
        'lastDataReceivedAt',
        'usage',
        'isDeactivated',
        'isUnavailable',
        'contractId',
        'professionalInstall',
        'ownership',
        'hasCameras',
    ];

    public const EDITABLE_FIELDS = [
        'vendor',
        'model',
        'team',
        'sn',
        'status',
        'hw',
        'sw',
        'port',
        'imei',
        'phone',
        'imsi',
        'username',
        'password',
        'updatedAt',
        'updatedBy',
        'blockingMessage',
        'isFixWithSpeed',
        'usage',
        'isDeactivated',
        'isUnavailable',
        'contractId',
        'professionalInstall',
        'ownership',
    ];

    public const CLIENT_EDITABLE_FIELDS = [
        'isDeactivated',
        'isUnavailable',
        'blockingMessage',
        'vehicleAction'
    ];

    /**
     * Device constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->model = $fields['model'] ?? null;
        $this->sn = $fields['sn'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_IN_STOCK;
        $this->team = $fields['team'] ?? null;
        $this->port = $fields['port'] ?? null;
        $this->hw = $fields['hw'] ?? null;
        $this->sw = $fields['sw'] ?? null;
        $this->imei = $fields['imei'] ?? null;
        $this->phone = $fields['phone'] ?? null;
        $this->imsi = $fields['imsi'] ?? null;
        $this->devEui = $fields['devEui'] ?? null;
        $this->iccid = $fields['iccid'] ?? null;
        $this->username = $fields['userName'] ?? null;
        $this->password = $fields['password'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->isFixWithSpeed = $fields['isFixWithSpeed'] ?? false;
        $this->notes = new ArrayCollection();
        $this->deviceInstallations = new ArrayCollection();
        $this->trackerRecords = new ArrayCollection();
        $this->trackerCommands = new ArrayCollection();
        $this->trackerSensors = new ArrayCollection();
        $this->trackerSensorRecords = new ArrayCollection();
        $this->deviceSensorHistories = new ArrayCollection();
        $this->trackerJammerRecords = new ArrayCollection();
        $this->usage = $fields['usage'] ?? self::USAGE_VEHICLE;
        $this->isDeactivated = (bool)($fields['isDeactivated'] ?? false);
        $this->isUnavailable = (bool)($fields['isUnavailable'] ?? false);
        $this->contractFinishAt = $fields['contractFinishAt'] ?? null;
        $this->contractStartAt = $fields['contractStartAt'] ?? null;
        $this->contractId = $fields['contractId'] ?? null;
        $this->professionalInstall = $fields['professionalInstall'] ?? false;
        $this->ownership = isset($fields['ownership']) && $fields['ownership'] ? strtolower($fields['ownership']) : null;
        $this->streamaxIntegration = $fields['streamaxIntegration'] ?? null;
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
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('vendor', $include, true)) {
            $data['vendor'] = $this->getVendor()->toArray(['id'], $user);
        }
        $data = $this->getNestedFields('vendor', $include, $data, $user);

        if (in_array('model', $include, true)) {
            $data['model'] = $this->getModel()->toArray(['protocol'], $user);
        }
        $data = $this->getNestedFields('model', $include, $data, $user);

        if (in_array('sn', $include, true)) {
            $data['sn'] = $this->sn;
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getProcessedStatus();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }
        $data = $this->getNestedFields('team', $include, $data, $user);

        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->team ? $this->getTeam()->getId() : null;
        }
        if (in_array('port', $include, true)) {
            $data['port'] = $this->getPort();
        }
        if (in_array('hw', $include, true)) {
            $data['hw'] = $this->getHw();
        }
        if (in_array('sw', $include, true)) {
            $data['sw'] = $this->getSw();
        }
        if (in_array('protocol', $include, true)) {
            $data['protocol'] = $this->getProtocol();
        }
        if (in_array('imei', $include, true)) {
            $data['imei'] = $this->imei;
        }
        if (in_array('phone', $include, true)) {
            $data['phone'] = $this->phone;
        }
        if (in_array('imsi', $include, true)) {
            $data['imsi'] = $this->getImsi();
        }
        if (in_array('devEui', $include, true)) {
            $data['devEui'] = $this->getDevEui();
        }
        if (in_array('iccid', $include, true)) {
            $data['iccid'] = $this->getIccid();
        }
        if (in_array('userName', $include, true)) {
            $data['userName'] = $this->username;
        }
        if (in_array('password', $include, true)) {
            $data['password'] = $this->password;
        }
        if (in_array('installDate', $include, true)) {
            $data['installDate'] = $this->formatDate($this->getInstallDate());
        }
        if (in_array('uninstallDate', $include, true)) {
            $data['uninstallDate'] = $this->formatDate($this->getUninstallDate());
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle()?->getId();
        }
        if (in_array('deviceInstallation', $include, true)) {
            $data['deviceInstallation'] = $this->getDeviceInstallation()
                ? $this->getDeviceInstallation()->toArray()
                : null;
        }

        $data = $this->getNestedFields('deviceInstallation', $include, $data);

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy() ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('trackerData', $include, true) || $this->getNestedIncludeByPrefix('trackerData', $include)) {
            $data['trackerData'] = $this->getTrackerData($this->getNestedIncludeByPrefix('trackerData', $include));
        }
        if (in_array('blockingMessage', $include, true)) {
            $data['blockingMessage'] = $this->getBlockingMessage();
        }
        if (in_array('lastActiveTime', $include, true)) {
            $data['lastActiveTime'] = $this->formatDate($this->getLastDataReceivedAt());
        }
        if (in_array('isFixWithSpeed', $include, true) && $this->isAllowedFixWithSpeed()) {
            $data['isFixWithSpeed'] = $this->isFixWithSpeed();
        }
        if (in_array('trackerCommands', $include, true)) {
            $data['trackerCommands'] = $this->getTrackerCommandsArray();
        }
        if (in_array('trackerSensors', $include, true)) {
            $data['trackerSensors'] = $this->getTrackerSensorsWithActualDataArray();
        }
        if (in_array('statusUpdatedAt', $include, true)) {
            $data['statusUpdatedAt'] = $this->formatDate($this->getStatusUpdatedAt());
        }
        if (in_array('lastDataReceivedAt', $include, true)) {
            $data['lastDataReceivedAt'] = $this->formatDate($this->getLastDataReceivedAt());
        }
        if (in_array('statusExt', $include, true)) {
            $data['statusExt'] = $this->getStatusExt();
        }
        if (in_array('usage', $include, true)) {
            $data['usage'] = $this->getUsage();
        }
        if (in_array('isDeactivated', $include, true)) {
            $data['isDeactivated'] = $this->getIsDeactivated();
        }
        if (in_array('isUnavailable', $include, true)) {
            $data['isUnavailable'] = $this->getIsUnavailable();
        }
        if (in_array('contractFinishAt', $include, true)) {
            $data['contractFinishAt'] = $this->formatDate($this->getContractFinishAt());
        }
        if (in_array('contractStartAt', $include, true)) {
            $data['contractStartAt'] = $this->formatDate($this->getContractStartAt());
        }
        if (in_array('addedToTeam', $include, true)) {
            $data['addedToTeam'] = $this->formatDate($this->getAddedToTeam());
        }
        if (in_array('contractId', $include, true)) {
            $data['contractId'] = $this->getContractId();
        }
        if (in_array('professionalInstall', $include, true)) {
            $data['professionalInstall'] = $this->isProfessionalInstall();
        }
        if (in_array('ownership', $include, true)) {
            $data['ownership'] = $this->getOwnership();
        }
        if (in_array('deactivatedAt', $include, true)) {
            $data['deactivatedAt'] = $this->formatDate($this->getDeactivatedAt());
        }
        if (in_array('plan', $include, true)) {
            $data['plan'] = $this->getPlanData();
        }
        if (in_array('reseller', $include, true)) {
            $data['reseller'] = $this->getReseller()?->toArray(Reseller::SIMPLE_VALUES);
        }
        if (in_array('hasCameras', $include, true)) {
            $data['hasCameras'] = TrackerStreamService::hasCameras($this);
        }

        return $data;
    }

    /**
     * @param array $include
     * @param User|null $user
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }
        if (in_array('imei', $include, true)) {
            $data['imei'] = $this->imei;
        }
        if (in_array('imsi', $include, true)) {
            $data['imsi'] = $this->imsi;
        }
        if (in_array('iccid', $include, true)) {
            $data['iccid'] = $this->getIccid();
        }
        if (in_array('phone', $include, true)) {
            $data['phone'] = $this->phone;
        }
        if (in_array('vendor', $include, true)) {
            $data['vendor'] = ($user && !$user->isInAdminTeam()) ? $this->getVendorAlias() : $this->getVendorName();
        }
        if (in_array('model', $include, true)) {
            $data['model'] = ($user && !$user->isInAdminTeam()) ? $this->getModelAlias() : $this->getModelName();
        }
        if (in_array('usage', $include, true)) {
            $data['usage'] = $this->getUsage();
        }
        if (in_array('vehicleRegNo', $include, true)) {
            $data['vehicleRegNo'] = $this->getVehicleField('regNo');
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('statusExt', $include, true)) {
            $data['statusExt'] = $this->getStatusExt();
        }
        if (in_array('isDeactivated', $include, true)) {
            $data['isDeactivated'] = $this->getIsDeactivated() ? 'Deactivated' : 'Active';
        }
        if (in_array('installDate', $include, true)) {
            $data['installDate'] = $this->getInstalledDateFormatted($user);
        }
        if (in_array('addedToTeam', $include, true)) {
            $data['addedToTeam'] = $this->formatDate(
                $this->getAddedToTeam(), self::EXPORT_DATE_WITHOUT_TIME_FORMAT, $user->getTimezone()
            );
        }
        if (in_array('deactivatedAt', $include, true)) {
            $data['deactivatedAt'] = $this->formatDate(
                $this->getDeactivatedAt(), self::EXPORT_DATE_WITHOUT_TIME_FORMAT, $user->getTimezone()
            );
        }
        if (in_array('contractFinishAt', $include, true)) {
            $data['contractFinishAt'] = $this->formatDate(
                $this->getContractFinishAt(), self::EXPORT_DATE_WITHOUT_TIME_FORMAT, $user->getTimezone()
            );
        }
        if (in_array('contractStartAt', $include, true)) {
            $data['contractStartAt'] = $this->formatDate(
                $this->getContractStartAt(), self::EXPORT_DATE_WITHOUT_TIME_FORMAT, $user->getTimezone()
            );
        }
        if (in_array('contractId', $include, true)) {
            $data['contractId'] = $this->getContractId();
        }
        if (in_array('lastActiveTime', $include, true)) {
            $data['lastActiveTime'] =
                $this->formatDate($this->getLastDataReceivedAt(), self::EXPORT_DATE_FORMAT, $user->getTimezone());
        }
        if (in_array('sn', $include, true)) {
            $data['sn'] = $this->sn;
        }
        if (in_array('client', $include, true)) {
            $data['client'] = $this->team ? $this->getTeam()->getClientName() : null;
        }
        if (in_array('port', $include, true)) {
            $data['port'] = $this->getPort();
        }
        if (in_array('hw', $include, true)) {
            $data['hw'] = $this->hw;
        }
        if (in_array('sw', $include, true)) {
            $data['sw'] = $this->sw;
        }
        if (in_array('devEui', $include, true)) {
            $data['devEui'] = $this->devEui;
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt(), self::EXPORT_DATE_FORMAT);
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy() ? $this->getCreatedBy()->getFullName() : null;
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt(), self::EXPORT_DATE_FORMAT);
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy()?->getFullName();
        }
        if (in_array('lastDataReceivedAt', $include, true)) {
            $data['lastDataReceivedAt'] = $this->formatDate($this->getLastDataReceivedAt(), self::EXPORT_DATE_FORMAT);
        }
        if (in_array('chevronAccountId', $include, true)) {
            $data['chevronAccountId'] = $this->getTeam()?->getChevronAccountId();
        }
        if (in_array('plan', $include, true)) {
            $data['plan'] = $this->getPlanData()['displayName'] ?? null;
        }
        if (in_array('reseller', $include, true)) {
            $data['reseller'] = $this->getReseller()?->getCompanyName();
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
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'DeviceModel')]
    #[ORM\JoinColumn(name: 'model_id', referencedColumnName: 'id')]
    private $model;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sn', type: 'string', length: 255, nullable: true)]
    private $sn;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100, nullable: true)]
    private $status = self::STATUS_IN_STOCK;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'devices')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var Vehicle|null
     */
    #[ORM\OneToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var int
     */
    #[ORM\Column(name: 'port', type: 'integer', nullable: true)]
    private $port;

    /**
     * @var string
     */
    #[ORM\Column(name: 'hw', type: 'string', length: 255, nullable: true)]
    private $hw;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sw', type: 'string', length: 255, nullable: true)]
    private $sw;

    /**
     * @var string
     *
     * @Assert\Unique()
     */
    #[ORM\Column(name: 'imei', type: 'string', length: 255, nullable: true)]
    private $imei;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    private $phone;

    /**
     * @var string
     */
    #[ORM\Column(name: 'imsi', type: 'string', length: 255, nullable: true)]
    private $imsi;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'devEui', type: 'string', length: 255, nullable: true)]
    private $devEui;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'iccid', type: 'string', length: 255, nullable: true)]
    private $iccid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: true)]
    private $username;

    /**
     * @var string
     */
    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: true)]
    private $password;

    /**
     * @var int
     */
    #[ORM\OneToMany(targetEntity: 'Note', mappedBy: 'device')]
    private $notes;

    private $deviceInstallation;

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
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var int
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerAuth', mappedBy: 'device')]
    private $trackerAuth;

    /**
     * @var array
     */
    private $trackerData;

    /**
     * @var TrackerHistoryLast|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Tracker\TrackerHistoryLast', mappedBy: 'device')]
    #[ORM\JoinColumn(name: 'tracker_history_last_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $lastTrackerRecord;

    /**
     * @var ArrayCollection|TrackerHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistory', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerRecords;

    /**
     * @var ArrayCollection|TrackerHistorySensor[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerSensorRecords;

    /**
     * @var string
     */
    #[ORM\Column(name: 'blocking_message', type: 'text', nullable: true)]
    private $blockingMessage;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_fix_with_speed', type: 'boolean', options: ['default' => '0'])]
    private $isFixWithSpeed = false;

    /**
     * @var array
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceInstallation', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $deviceInstallations;

    private $gpsStatusDuration = Client::DEFAULT_GPS_STATUS_DURATION;

    /**
     * @var ArrayCollection|VehicleOdometer[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\VehicleOdometer', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $odometerData;

    /**
     * @var ArrayCollection|TrackerCommand[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerCommand', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerCommands;

    /**
     * @var ArrayCollection|DeviceSensor[]|null
     *
     * @Assert\NotBlank(groups={"editDeviceSensors"})
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceSensor', mappedBy: 'device', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $trackerSensors;

    /**
     * @var ArrayCollection|DeviceSensorHistory[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceSensorHistory', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $deviceSensorHistories;

    /**
     * @var ArrayCollection|Route[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Route', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $routes;

    /**
     * @var ArrayCollection|TrackerHistoryDTCVIN[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistoryDTCVIN', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerDTCVINRecords;

    /**
     * @var ArrayCollection|DrivingBehavior[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DrivingBehavior', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerDrivingBehaviorRecords;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_device_id', type: 'integer', nullable: true)]
    private $traccarDeviceId;

    /**
     * @var ArrayCollection|TraccarEventHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TraccarEventHistory', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $traccarEventHistories;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'status_updated_at', type: 'datetime', nullable: true)]
    private $statusUpdatedAt;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'protocol', type: 'string', length: 255, nullable: true)]
    private $protocol;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'status_ext', type: 'string', length: 50, nullable: true)]
    private $statusExt = self::STATUS_EXT_OFFLINE;

    /**
     * @var \DateTime|null
     */
    private $lastDataReceivedAt;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'usage', type: 'string', nullable: true, options: ['default' => self::USAGE_VEHICLE])]
    private $usage;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_deactivated', type: 'boolean', options: ['default' => '0'])]
    private $isDeactivated;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_unavailable', type: 'boolean', options: ['default' => '0'])]
    private $isUnavailable;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'contract_finish_at', type: 'datetime', nullable: true)]
    private $contractFinishAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'contract_start_at', type: 'datetime', nullable: true)]
    private $contractStartAt;

    private EntityManager $em;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'contract_id', type: 'string', nullable: true)]
    private $contractId;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'professional_install', type: 'boolean', options: ['default' => '0'], nullable: false)]
    private $professionalInstall;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'ownership', type: 'string', length: 50, nullable: true)]
    private $ownership;

    /**
     * @var ArrayCollection|TrackerHistoryJammer[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistoryJammer', mappedBy: 'device', fetch: 'EXTRA_LAZY')]
    private $trackerJammerRecords;

    /**
     * @var StreamaxIntegration|null
     */
    #[ORM\ManyToOne(targetEntity: 'StreamaxIntegration', inversedBy: 'devices')]
    #[ORM\JoinColumn(name: 'streamax_integration_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?StreamaxIntegration $streamaxIntegration;

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
     * Get vendor
     *
     * @return DeviceVendor
     */
    public function getVendor(): DeviceVendor
    {
        return $this->getModel()->getVendor();
    }


    /**
     * Get vendor name.
     *
     * @return string|null
     */
    public function getVendorName(): ?string
    {
        return $this->getVendor()?->getName();
    }

    /**
     * Set model
     *
     * @param DeviceModel $model
     *
     * @return Device
     */
    public function setModel(DeviceModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return DeviceModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get model name
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->getModel()?->getName();
    }

    /**
     * @return string|null
     */
    public function getModelAlias(): ?string
    {
        return $this->getModel()?->getAlias();
    }

    /**
     * @return string|null
     */
    public function getVendorAlias(): ?string
    {
        return $this->getVendor()?->getAlias();
    }

    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return Device
     */
    public function setSn($sn)
    {
        $this->sn = $sn;

        return $this;
    }

    /**
     * Get sn
     *
     * @return string
     */
    public function getSn()
    {
        return $this->sn;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Device
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

    public function getProcessedStatus()
    {
        if ($this->status !== self::STATUS_DELETED && $this->isUnavailable) {
            return self::STATUS_UNAVAILABLE;
        }
        if ($this->status === self::STATUS_IN_STOCK && $this->getTeam()->isAdminTeam()) {
            return self::STATUS_IN_STOCK_ADMIN;
        }
        if ($this->status === self::STATUS_IN_STOCK && $this->getTeam()->isResellerTeam()) {
            return self::STATUS_IN_STOCK_RESELLER;
        }

        return $this->status;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return Device
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

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
     * @return int|null
     */
    public function getTeamId(): ?int
    {
        return $this->getTeam() ? $this->getTeam()->getId() : null;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return Device
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return (int)$this->port;
    }

    /**
     * Set hw
     *
     * @param string $hw
     *
     * @return Device
     */
    public function setHw($hw)
    {
        $this->hw = $hw;

        return $this;
    }

    /**
     * Get hw
     *
     * @return string
     */
    public function getHw()
    {
        return $this->hw;
    }

    /**
     * Set sw
     *
     * @param string $sw
     *
     * @return Device
     */
    public function setSw($sw)
    {
        $this->sw = $sw;

        return $this;
    }

    /**
     * Get sw
     *
     * @return string
     */
    public function getSw()
    {
        return $this->sw;
    }

    /**
     * Set imei
     *
     * @param string $imei
     *
     * @return Device
     */
    public function setImei($imei)
    {
        $this->imei = $imei;

        return $this;
    }

    /**
     * Get imei
     *
     * @return string
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Device
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return !empty($this->phone) ? $this->phone : null;
    }

    /**
     * Set imsi
     *
     * @param string $imsi
     *
     * @return Device
     */
    public function setImsi($imsi)
    {
        $this->imsi = $imsi;

        return $this;
    }

    /**
     * Get imsi
     *
     * @return string
     */
    public function getImsi()
    {
        return $this->imsi;
    }

    /**
     * Set devEui
     *
     * @param string $devEui
     *
     * @return Device
     */
    public function setDevEui($devEui)
    {
        $this->devEui = $devEui;

        return $this;
    }

    /**
     * Get devEui
     *
     * @return string
     */
    public function getDevEui()
    {
        return $this->devEui;
    }

    /**
     * @return string|null
     */
    public function getIccid(): ?string
    {
        return $this->iccid;
    }

    /**
     * @param string|null $iccid
     * @return Device
     */
    public function setIccid(?string $iccid): self
    {
        $this->iccid = $iccid;

        return $this;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Device
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Device
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get installDate
     *
     * @return string
     */
    public function getInstallDate()
    {
        return $this->getDeviceInstallation()?->getInstallDate();
    }

    /**
     * @return \DateTime|null
     */
    public function getInstallDateFromFirstInstallation(): ?\DateTime
    {
        return $this->getFirstDeviceInstallation()?->getInstallDate();
    }

    public function getUninstallDate()
    {
        return $this->getLastDeviceInstallation()
            ? $this->getLastDeviceInstallation()->getUninstallDate()
            : null;
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return array_map(
            function ($note) {
                return $note;
            },
            $this->notes->toArray()
        );
    }

    /**
     * @param Note $note
     */
    public function addNote(Note $note)
    {
        $this->notes->add($note);
    }

    /**
     * @param Vehicle|null $vehicle
     *
     * @return Device
     */
    public function setVehicle(?Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getVehicle()?->getId();
    }

    /**
     * @return User|null
     */
    public function getVehicleDriver(): ?User
    {
        return $this->getVehicle()?->getDriver();
    }

    /**
     * @return string|null
     */
    public function getFuelType()
    {
        $vehicle = $this->getVehicle();

        return $vehicle ? $vehicle->getFuelType() : null;
    }

    /**
     * @return float|null
     */
    public function getFuelTankCapacity()
    {
        $vehicle = $this->getVehicle();

        return $vehicle ? $vehicle->getFuelTankCapacity() : null;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getVehicleField(string $field)
    {
        $method = 'get' . ucfirst($field);
        return $this->getVehicle() && method_exists($this->getVehicle(), $method)
            ? $this->getVehicle()->$method()
            : null;
    }

    /**
     * @param DeviceInstallation $deviceInstallation
     * @return Device
     */
    public function install(DeviceInstallation $deviceInstallation)
    {
        $this->setVehicle($deviceInstallation->getVehicle());
        $deviceInstallation->getVehicle()->setDevice($this);

        $this->setDeviceInstallation($deviceInstallation);

        return $this;
    }

    /**
     * @return Device
     */
    public function uninstall()
    {
        if ($this->getDeviceInstallation() && $this->getDeviceInstallation()->getVehicle()) {
            $this->getDeviceInstallation()->getVehicle()->setDevice(null);
        }

        $this->setDeviceInstallation(null);
        $this->setVehicle(null);

        return $this;
    }

    /**
     * @param DeviceInstallation $value
     */
    public function setDeviceInstallation(?DeviceInstallation $value)
    {
        $this->deviceInstallation = $value;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Device
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string
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
     * @return Device
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

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Device
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
     * Get updatedAtFormatted
     *
     * @return string
     */
    public function getUpdatedAtFormatted()
    {
        return $this->updatedAt->format(self::EXPORT_DATE_FORMAT);
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return Device
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
     * @return mixed|null
     */
    public function getClient()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient()->getName() : null;
    }

    /**
     * @return Client|null
     */
    public function getClientEntity()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient() : null;
    }

    /**
     * @return mixed|null
     */
    public function getClientId()
    {
        return $this->getTeam()->getClientId();
    }

    /**
     * @return mixed|null
     */
    public function getLastActiveTime()
    {
        $trackerData = $this->getTrackerData();

        return $trackerData && $trackerData['lastDataReceived'] ? $trackerData['lastDataReceived'] : null;
    }

    /**
     * @return mixed|null
     */
    public function getMileage()
    {
        $trackerData = $this->getTrackerData();

        return $trackerData && $trackerData['mileage'] ? $trackerData['mileage'] : null;
    }

    /**
     * @return mixed|null
     */
    public function getEngineHours()
    {
        $trackerData = $this->getTrackerData();

        return $trackerData && $trackerData['engineHours'] ? $trackerData['engineHours'] : null;
    }

    /**
     * @param User $user
     * @return \DateTime|mixed|null
     * @throws \Exception
     */
    public function getInstalledDateFormatted(User $user)
    {
        return $this->getDeviceInstallation()
            ? $this->formatDate(
                $this->getDeviceInstallation()->getInstallDate(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            )
            : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getTrackerData(array $fields = []): ?array
    {
        if (!$this->trackerData) {
            $this->trackerData = $this->getTrackerDataForDevice($fields);
        }

        return $this->trackerData;
    }

    /**
     * @param array|null $trackerData
     */
    public function setTrackerData(?array $trackerData): void
    {
        $this->trackerData = $trackerData;
    }

    /**
     * @return string
     */
    public function getBlockingMessage(): ?string
    {
        return $this->blockingMessage;
    }

    /**
     * @param string $blockingMessage
     */
    public function setBlockingMessage(?string $blockingMessage): void
    {
        $this->blockingMessage = $blockingMessage;
    }

    /**
     * @return bool
     */
    public function isInStock()
    {
        return $this->status === self::STATUS_IN_STOCK;
    }

    /**
     * @param int $seconds
     * @return bool|null
     */
    public function getGPSStatusByDuration($seconds): ?bool
    {
        $lastRecordCreatedAt = $this->getLastTrackerRecord()->getCreatedAt();
        $dateAgo = Carbon::now()->subSeconds($seconds);

        return $lastRecordCreatedAt ? $lastRecordCreatedAt > $dateAgo : false;
    }

    /**
     * @return TrackerHistoryLast|null
     */
    public function getLastTrackerRecord(): ?TrackerHistoryLast
    {
        return $this->lastTrackerRecord;
    }

    /**
     * @return float|null
     */
    public function getLastTrackerRecordOdometer(): ?float
    {
        return $this->getLastTrackerRecord()?->getOdometer();
    }

    /**
     * @return TrackerHistory|null
     */
    public function getLastTrackerHistory(): ?TrackerHistory
    {
        return $this->getLastTrackerRecord()?->getTrackerHistory();
    }

    /**
     * @return int|null
     */
    public function getLastTrackerHistoryId(): ?int
    {
        return $this->getLastTrackerHistory() ? $this->getLastTrackerHistory()->getId() : null;
    }

    /**
     * @param TrackerHistoryLast|null $lastTrackerRecord
     */
    public function setLastTrackerRecord(?TrackerHistoryLast $lastTrackerRecord): void
    {
        $this->lastTrackerRecord = $lastTrackerRecord;
    }

    /**
     * @return bool
     */
    public function isAvailableForEditStatus(): bool
    {
        return !in_array(
            $this->getStatus(),
            [
                Device::STATUS_IN_STOCK,
                Device::STATUS_UNAVAILABLE,
                Device::STATUS_DELETED,
            ]
        );
    }

    /**
     * @return bool
     */
    public function isFixWithSpeed(): bool
    {
        return $this->isFixWithSpeed;
    }

    /**
     * @param bool $isFixWithSpeed
     */
    public function setIsFixWithSpeed(bool $isFixWithSpeed): void
    {
        $this->isFixWithSpeed = $isFixWithSpeed;
    }

    /**
     * @return bool
     */
    public function isAllowedFixWithSpeed(): bool
    {
        return match ($this->getVendorName()) {
            DeviceVendor::VENDOR_PIVOTEL => false,
            default => true
        };
    }

    public function isFixWithSpeedByDefault(): bool
    {
        return match ($this->getModelName()) {
            DeviceModel::TRACCAR_MEITRACK_P99G,
            DeviceModel::TRACCAR_MEITRACK_P99L => true,
            default => false
        };
    }

    /**
     * @return DeviceInstallation|null
     */
    public function getDeviceInstallation(): ?DeviceInstallation
    {
        if (!$this->deviceInstallation) {
            $deviceInstallation = $this->deviceInstallations->matching(
                Criteria::create()
                    ->where(Criteria::expr()->isNull('uninstallDate'))
                    ->setMaxResults(1)
            )->first();

            if ($deviceInstallation) {
                $this->deviceInstallation = $deviceInstallation;
                $this->install($deviceInstallation);
            }
        }

        return $this->deviceInstallation;
    }

    /**
     * @return DeviceInstallation|null
     */
    public function getFirstDeviceInstallation(): ?DeviceInstallation
    {
        $deviceInstallation = $this->deviceInstallations->matching(
            Criteria::create()
                ->orderBy(['installDate' => Criteria::ASC])
                ->setMaxResults(1)
        )->first();

        return $deviceInstallation ?: null;
    }

    public function getLastDeviceInstallation(): ?DeviceInstallation
    {
        $deviceInstallation = $this->deviceInstallations->matching(
            Criteria::create()
                ->setMaxResults(1)
                ->orderBy(['installDate' => Criteria::DESC])
        )->first();

        return $deviceInstallation ?: null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getTrackerDataForDevice(array $fields = []): ?array
    {
        $lastTrackerRecord = $this->getLastTrackerRecord();
        $lastTrackerData = [];

        if ($lastTrackerRecord && $lastTrackerRecord->getTrackerHistory()) {
            $lastValidGpsRecord = $this->getValidPreviousPoint($lastTrackerRecord->getTrackerHistory());

            if ($lastValidGpsRecord) {
                $lastTrackerRecord->setLng($lastValidGpsRecord->getLng());
                $lastTrackerRecord->setLat($lastValidGpsRecord->getLat());
            }

            $lastTrackerData = $lastTrackerRecord->toArray($fields);
        }

        $deviceData = $this->getSensorDataForDevice($this->gpsStatusDuration);

        return $lastTrackerData ? array_merge($lastTrackerData, $deviceData) : null;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return TrackerHistory|null
     */
    public function getValidPreviousPoint(TrackerHistory $trackerHistory)
    {
        if (!(double)$trackerHistory->getLat() || !(double)$trackerHistory->getLng()) {
            $point = $this->getPreviousThWithCoordinates($trackerHistory);

            if ($point && (double)$point->getLat() && (double)$point->getLng()) {
                return $point;
            }
        } else {
            return $trackerHistory;
        }

        return null;
    }

    /**
     * @return TrackerHistory[]|ArrayCollection|null
     */
    public function getTrackerRecords(): ?Collection
    {
        return $this->trackerRecords;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return TrackerHistory|null
     */
    public function getPreviousThWithCoordinates(TrackerHistory $trackerHistory): ?TrackerHistory
    {
        return $this->trackerRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->lt('ts', $trackerHistory->getTs()))
                ->andWhere(Criteria::expr()->neq('lat', 0))
                ->andWhere(Criteria::expr()->neq('lng', 0))
                ->andWhere(Criteria::expr()->neq('lat', null))
                ->andWhere(Criteria::expr()->neq('lng', null))
                ->orderBy(['ts' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return TrackerHistory|null
     */
    public function getFutureThWithCoordinates(TrackerHistory $trackerHistory): ?TrackerHistory
    {
        return $this->trackerRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->gt('ts', $trackerHistory->getTs()))
                ->andWhere(Criteria::expr()->neq('lat', 0))
                ->andWhere(Criteria::expr()->neq('lng', 0))
                ->andWhere(Criteria::expr()->neq('lat', null))
                ->andWhere(Criteria::expr()->neq('lng', null))
                ->orderBy(['ts' => Criteria::ASC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return TrackerHistory|null
     */
    public function getValidFuturePoint(TrackerHistory $trackerHistory)
    {
        if (!(double)$trackerHistory->getLat() || !(double)$trackerHistory->getLng()) {
            $point = $this->getFutureThWithCoordinates($trackerHistory);

            if ($point && (double)$point->getLat() && (double)$point->getLng()) {
                return $point;
            }
        } else {
            return $trackerHistory;
        }

        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRecordCreatedAtByDevice(): ?\DateTime
    {
        $result = $this->trackerRecords->matching(
            Criteria::create()
                ->orderBy(['createdAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return $result ? $result->getCreatedAt() : null;
    }

    /**
     * @param $gpsStatusDuration
     * @return array
     */
    public function getSensorDataForDevice($gpsStatusDuration): array
    {
        $lastRecordCreatedAt = $this->getLastRecordCreatedAtByDevice();
        $dateAgo = Carbon::now()->subSeconds($gpsStatusDuration);

        return [
            'gpsStatus' => $lastRecordCreatedAt ? $lastRecordCreatedAt > $dateAgo : false,
            'sensorsData' => $this->getTrackerSensorsWithActualDataArray()
        ];
    }

    /**
     * @param $gpsStatusDuration
     */
    public function setGpsStatusDuration($gpsStatusDuration)
    {
        $this->gpsStatusDuration = $gpsStatusDuration;
    }

    /**
     * @param $ts
     * @return bool
     */
    public function recordExistsForDevice($ts): bool
    {
        return (bool)$this->trackerRecords->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('ts', $ts))
        )->first();
    }

    /**
     * @param $date
     * @return TrackerHistory|null
     */
    public function getLastTrackerRecordByDate($date): ?TrackerHistory
    {
        return $this->getTrackerRecords()->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('ts', $date))
                ->orderBy(['ts' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @param $occurredAt
     * @return int
     */
    public function sensorRecordExistsForDevice($occurredAt)
    {
        return $this->trackerSensorRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('occurredAt', $occurredAt))
        )->count();
    }

    /**
     * @param $occurredAt
     * @return int
     */
    public function DTCVINRecordExistsForDevice($occurredAt)
    {
        return $this->trackerDTCVINRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('occurredAt', $occurredAt))
        )->count();
    }

    /**
     * @param $occurredAt
     * @param string $eventType
     * @return int
     */
    public function traccarEventRecordExistsForDevice($occurredAt, string $eventType)
    {
        return $this->traccarEventHistories->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('type', $eventType))
                ->andWhere(Criteria::expr()->eq('occurredAt', $occurredAt))
        )->count();
    }

    /**
     * @param $occurredAt
     * @return int
     */
    public function drivingBehaviorRecordExistsForDevice($occurredAt)
    {
        return $this->trackerDrivingBehaviorRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('ts', $occurredAt))
        )->count();
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getClientEntity()
            ? $this->getClientEntity()->getTimeZoneName()
            : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @param $odometer
     * @param Vehicle|null $vehicle
     * @return float|null
     */
    public function getLastCorrectedOdometerValue($odometer, ?Vehicle $vehicle): ?float
    {
        if (!$this->odometerData) {
            return $odometer;
        }

        $result = $this->odometerData->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('odometer', null))
                ->andWhere(Criteria::expr()->eq('vehicle', $vehicle))
                ->orderBy(['occurredAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first();

        return ($result && !is_null($odometer) && !is_null($result->getAccuracy()))
            ? $odometer + $result->getAccuracy()
            : $odometer;
    }

    /**
     * @return Collection|null
     */
    public function getTrackerCommands(): ?Collection
    {
        return $this->trackerCommands;
    }

    /**
     * @return Collection|null
     */
    public function getTrackerCommandsSorted(): ?Collection
    {
        return $this->trackerCommands->matching(
            Criteria::create()
                ->orderBy(['createdAt' => Criteria::ASC, 'id' => Criteria::ASC])
        ) ?: null;
    }

    /**
     * @return array|null
     */
    public function getTrackerCommandsArray(): ?array
    {
        return array_map(
            function (TrackerCommand $trackerCommand) {
                return $trackerCommand->toArray();
            },
            $this->getTrackerCommandsSorted()->toArray()
        );
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getTrackerCommandToSend(): ?string
    {
        $command = $this->getNotSentTrackerCommands()->first();

        if ($command) {
            $command->setSentAt(new \DateTime());

            return $command->getCommandRequest();
        }

        return null;
    }

    /**
     * @return Collection|null
     */
    public function getNotSentTrackerCommands(): ?Collection
    {
        return $this->trackerCommands->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('sentAt', null))
                ->orderBy(['createdAt' => Criteria::ASC, 'id' => Criteria::ASC])
        ) ?: null;
    }

    /**
     * @return Collection|null
     */
    public function getSentNotRespondedTrackerCommands(): ?Collection
    {
        return $this->trackerCommands->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('sentAt', null))
                ->andWhere(Criteria::expr()->eq('respondedAt', null))
                ->orderBy(['sentAt' => Criteria::ASC, 'id' => Criteria::ASC])
        ) ?: null;
    }

    /**
     * @return TrackerCommand|null
     */
    public function getOldestSentNotRespondedTrackerCommand(): ?TrackerCommand
    {
        return $this->getSentNotRespondedTrackerCommands()->first() ?: null;
    }

    /**
     * @return array|null
     */
    public function getTrackerCommandsRequests(): ?array
    {
        return $this->getNotSentTrackerCommands()
            ->map(
                function (TrackerCommand $trackerCommand) {
                    return $trackerCommand->getCommandRequest();
                }
            )->getValues() ?: null;
    }

    /**
     * @param TrackerCommand $trackerCommand
     * @return void
     */
    public function addTrackerCommand(TrackerCommand $trackerCommand)
    {
        if (!$this->trackerCommands->contains($trackerCommand)) {
            $this->trackerCommands->add($trackerCommand);
        }
    }

    /**
     * @return DeviceSensor[]|Collection|null
     */
    public function getTrackerSensors(): ?Collection
    {
        return $this->trackerSensors
            ->matching(Criteria::create()
                ->where(Criteria::expr()->neq('status', DeviceSensor::STATUS_DELETED))
            );
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @return void
     */
    public function addTrackerSensor(DeviceSensor $deviceSensor): void
    {
        if (!$this->trackerSensors->contains($deviceSensor)) {
            $this->trackerSensors->add($deviceSensor);
        }
    }

    /**
     * @param DeviceSensor $deviceSensor
     */
    public function removeTrackerSensor(DeviceSensor $deviceSensor)
    {
        if ($this->trackerSensors->contains($deviceSensor)) {
            $this->trackerSensors->removeElement($deviceSensor);
        }
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @return bool
     */
    public function hasTrackerSensor(DeviceSensor $deviceSensor): bool
    {
        return $this->trackerSensors->contains($deviceSensor);
    }

    /**
     * @return Collection|null
     */
    public function getTrackerSensorsWithActualData(): ?Collection
    {
        return $this->getTrackerSensors()
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->neq('lastTrackerHistorySensor', null))
                    ->andWhere(Criteria::expr()->neq('rssi', null))
                    ->andWhere(Criteria::expr()->neq('lastOccurredAt', null))
                    ->andWhere(Criteria::expr()->gte(
                        'lastOccurredAt', (new Carbon())->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME)
                    ))
            )
            ->filter(function (DeviceSensor $deviceSensor) {
                $sensor = $deviceSensor->getSensor();

                if (
                    $sensor->hasStrongerOrEqualDeviceSensorByRSSI($deviceSensor) ||
                    $sensor->hasDeviceSensorWithNewestData($deviceSensor)
                ) {
                    return false;
                }

                return true;
            });
    }

    /**
     * @return int
     */
    public function getRSSIAccuracy(): int
    {
        switch ($this->getVendorName()) {
            case DeviceVendor::VENDOR_TELTONIKA:
            case DeviceVendor::VENDOR_ULBOTECH:
            case DeviceVendor::VENDOR_TOPFLYTECH:
            default:
                return BaseBLE::RSSI_ACCURACY;
        }
    }

    /**
     * @return int
     */
    public function getRSSIAccuracyTime(): int
    {
        switch ($this->getVendorName()) {
            case DeviceVendor::VENDOR_TELTONIKA:
            case DeviceVendor::VENDOR_ULBOTECH:
            case DeviceVendor::VENDOR_TOPFLYTECH:
            default:
                return BaseBLE::RSSI_ACCURACY_TIME;
        }
    }

    /**
     * @return array|null
     */
    public function getTrackerSensorsWithActualDataArray(): ?array
    {
        return array_map(
            function (DeviceSensor $deviceSensor) {
                return $deviceSensor->toArray();
            },
            $this->getTrackerSensorsWithActualData()->getValues()
        );
    }

    /**
     * @return array|null
     */
    public function getTrackerSensorsArray(): ?array
    {
        return array_map(
            function (DeviceSensor $deviceSensor) {
                return $deviceSensor->toArray();
            },
            $this->getTrackerSensors()->getValues()
        );
    }

    /**
     * @return array|null
     */
    public function getTrackerSensorsIds(): ?array
    {
        return $this->getTrackerSensors()->map(
            function (DeviceSensor $deviceSensor) {
                return $deviceSensor->getSensorId();
            }
        )->toArray();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return Collection|null
     */
    public function getTrackerSensorsRecordsCollectionByRange($startDate, $endDate): ?Collection
    {
        return $this->trackerSensorRecords->matching(
            Criteria::create()
                ->where(Criteria::expr()->gte('occurredAt', $startDate))
                ->andWhere(Criteria::expr()->lte('occurredAt', $endDate))
                ->orderBy(['occurredAt' => Criteria::ASC])
        ) ?: null;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array|null
     */
    public function getTrackerSensorsRecordsByRange($startDate, $endDate): ?array
    {
        $sensorsRecordsCollection = $this->getTrackerSensorsRecordsCollectionByRange($startDate, $endDate);

        return $sensorsRecordsCollection ? array_map(
            function (TrackerHistorySensor $trackerHistorySensor) {
                return $trackerHistorySensor->toArray();
            },
            $sensorsRecordsCollection->toArray()
        ) : [];
    }

    public function getReseller(): ?Reseller
    {
        if ($this->getTeam()->getClient()?->getReseller()) {
            return $this->getTeam()->getClient()?->getReseller();
        }

        if ($this->getTeam()->isResellerTeam()) {
            return $this->getTeam()->getReseller();
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        $assets = [];
        /** @var DeviceSensor $deviceSensor */
        $deviceSensors = $this->getDeviceSensorsWithTypeHavingTemp();

        if (!$deviceSensors) {
            return [];
        }

        foreach ($deviceSensors as $deviceSensor) {
            $sensor = $deviceSensor->getSensor();

            if (!$sensor) {
                break;
            }

            $assets[] = $sensor->getAsset();
        }

        return $assets;
    }

    /**
     * @return Collection|DeviceSensor[]|null
     */
    public function getDeviceSensorsWithTypeHavingTemp(): ?Collection
    {
        $deviceSensorCollection = $this->getTrackerSensors()->matching(
            Criteria::create()
                ->orderBy(['createdAt' => Criteria::DESC])
        )->filter(function (DeviceSensor $deviceSensor) {
            $sensor = $deviceSensor->getSensor();

            if (!$sensor || !$sensor->isTypeWithTemperature()) {
                return false;
            }

            return boolval($sensor->getAsset());
        });

        return !$deviceSensorCollection->isEmpty() ? $deviceSensorCollection : null;
    }

    /**
     * @return Collection|Route[]|null
     */
    public function getRoutes(): ?Collection
    {
        return $this->routes;
    }

    /**
     * @param $date
     * @return Route|null
     */
    public function getLastRouteByDate($date): ?Route
    {
        return $this->getRoutes()->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('startedAt', $date))
                ->andWhere(Criteria::expr()->eq('type', Route::TYPE_STOP))
                ->orderBy(['startedAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @return array
     */
    public function toTraccarEntity(): array
    {
        return [
            'uniqueId' => $this->getImei(),
            'phone' => $this->getPhone(),
            'model' => $this->getModelName(),
            'contact' => $this->getUsername(),
            'name' => $this->getModelName() . ': ' . $this->getImei(),
        ];
    }

    /**
     * @return array
     */
    public function toStreamaxEntity(): array
    {
        return [
            'uniqueId' => $this->getImei(),
        ];
    }

    /**
     * @return int|null
     */
    public function getParserType(): ?int
    {
        return $this->getModel()->getParserType();
    }

    /**
     * @return int|null
     */
    public function getTraccarDeviceId(): ?int
    {
        return $this->traccarDeviceId;
    }

    /**
     * @param int|null $traccarDeviceId
     * @return self
     */
    public function setTraccarDeviceId(?int $traccarDeviceId): self
    {
        $this->traccarDeviceId = $traccarDeviceId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVehicle()
    {
        return (bool)$this->getVehicle();
    }

    /**
     * @return \DateTime|null
     */
    public function getStatusUpdatedAt(): ?\DateTime
    {
        return $this->statusUpdatedAt;
    }

    /**
     * @param \DateTime|null $statusUpdatedAt
     * @return Device
     */
    public function setStatusUpdatedAt(?\DateTime $statusUpdatedAt)
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    /**
     * @param string|null $protocol
     * @return Device
     */
    public function setProtocol(?string $protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusExt(): ?string
    {
        return $this->statusExt;
    }

    /**
     * @param string|null $statusExt
     * @return Device
     */
    public function setStatusExt(?string $statusExt): self
    {
        $this->statusExt = $statusExt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastDataReceivedAt(): ?\DateTime
    {
        return $this->lastDataReceivedAt;
    }

    /**
     * @param \DateTime|null $lastDataReceivedAt
     * @return Device
     */
    public function setLastDataReceivedAt(?\DateTime $lastDataReceivedAt): self
    {
        $this->lastDataReceivedAt = $lastDataReceivedAt;

        return $this;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function setUsage(?string $usage): self
    {
        $this->usage = $usage;

        return $this;
    }

    public function getDeactivation(): bool
    {
        return false;
    }

    public function setDeactivation(bool $deactivation): self
    {
        return $this;
    }

    public function setIsDeactivated(bool $isDeactivated): self
    {
        $this->isDeactivated = $isDeactivated;

        return $this;
    }

    public function getIsDeactivated(): bool
    {
        return $this->isDeactivated;
    }

    public function setIsUnavailable(bool $isUnavailable): self
    {
        $this->isUnavailable = $isUnavailable;

        return $this;
    }

    public function getIsUnavailable(): bool
    {
        return $this->isUnavailable;
    }

    public function setContractFinishAt(?\DateTime $contractFinishAt): self
    {
        $this->contractFinishAt = $contractFinishAt?->setTimezone(new \DateTimeZone('UTC'));

        return $this;
    }

    public function getContractFinishAt(): ?\DateTime
    {
        return $this->contractFinishAt;
    }

    public function setContractStartAt(?\DateTime $contractStartAt): self
    {
        $this->contractStartAt = $contractStartAt;

        return $this;
    }

    public function getContractStartAt(): ?\DateTime
    {
        return $this->contractStartAt;
    }

    public function isActiveContract(): bool
    {
        return $this->getContractFinishAt() && $this->getContractFinishAt() > (new \DateTime());
    }

    public function recalculateContractDate(): ?\DateTime
    {
        if ($this->getClient() && $this->getClientEntity()->getContractMonths()) {
            $this->setContractFinishAt((Carbon::now())->addMonths($this->getClientEntity()->getContractMonths()));
        } elseif ($this->getContractFinishAt()) {
            $this->setContractFinishAt(null);
        }

        return $this->getContractFinishAt();
    }

    /**
     * @return bool
     */
    public function isVendorHasExternalVoltage(): bool
    {
        return $this->getVendor()->hasExternalVoltage();
    }

    /**
     * @return bool
     */
    public function isVendorHasSatellites(): bool
    {
        return $this->getVendor()->hasSatellites();
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    public function getAddedToTeam(): ?\DateTimeInterface
    {
        /** @var BillingEntityHistory $beh */
        $beh = $this->em->getRepository(BillingEntityHistory::class)->findOneBy([
            'entityId' => $this->getId(),
            'entity' => BillingEntityHistory::ENTITY_DEVICE,
            'dateTo' => null,
            'type' => BillingEntityHistory::TYPE_CHANGE_TEAM
        ]);

        return $beh?->getDateFrom();
    }

    public function getDeactivatedAt(): ?\DateTimeInterface
    {
        /** @var BillingEntityHistory $beh */
        $beh = $this->em->getRepository(BillingEntityHistory::class)->findOneBy([
            'entityId' => $this->getId(),
            'entity' => BillingEntityHistory::ENTITY_DEVICE,
            'dateTo' => null,
            'type' => BillingEntityHistory::TYPE_DEACTIVATED
        ]);

        return $beh?->getDateFrom();
    }

    public function setContractId(string $contractId): self
    {
        $this->contractId = $contractId;

        return $this;
    }

    public function getContractId(): ?string
    {
        return $this->contractId;
    }

    public function getPreviousPhones(): array
    {
        $history = $this->em->getRepository(EntityHistory::class)
            ->findBy(['entityId' => $this->id, 'type' => EntityHistoryTypes::DEVICE_PHONE_CHANGED]);

        return array_map(fn(EntityHistory $item) => $item->getPayload(), $history);
    }

    /**
     * @return bool
     */
    public function isProfessionalInstall(): bool
    {
        return $this->professionalInstall;
    }

    /**
     * @param bool $professionalInstall
     */
    public function setProfessionalInstall(bool $professionalInstall): void
    {
        $this->professionalInstall = $professionalInstall;
    }

    /**
     * @return string|null
     */
    public function getOwnership(): ?string
    {
        return $this->ownership;
    }

    /**
     * @param string|null $ownership
     */
    public function setOwnership(?string $ownership): void
    {
        $this->ownership = $ownership ? strtolower($ownership) : null;
    }

    /**
     * @return array
     */
    public static function getAllowedOwnerships(): array
    {
        return [
            self::OWNERSHIP_LINXIO,
            self::OWNERSHIP_CLIENT,
        ];
    }

    /**
     * @param $occurredAt
     * @return int
     */
    public function jammerRecordExistsForDevice($occurredAt)
    {
        return $this->trackerJammerRecords->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('occurredAtOn', $occurredAt))
        )->count();
    }

    public function getPlan(): ?Plan
    {
        return $this->getClientEntity()?->getPlan();
    }

    public function getPlanData(): ?array
    {
        return $this->getPlan()?->toArray([], $this->getTeam());
    }

    /**
     * @return StreamaxIntegration|null
     */
    public function getStreamaxIntegration(): ?StreamaxIntegration
    {
        return $this->streamaxIntegration;
    }

    /**
     * @param StreamaxIntegration|null $streamaxIntegration
     */
    public function setStreamaxIntegration(?StreamaxIntegration $streamaxIntegration): void
    {
        $this->streamaxIntegration = $streamaxIntegration;
    }
}
