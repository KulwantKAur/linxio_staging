<?php

namespace App\Entity\Notification;

use App\Entity\Area;
use App\Entity\AreaHistory;
use App\Entity\Asset;
use App\Entity\BaseEntity;
use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DigitalForm;
use App\Entity\DigitalFormAnswer;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\EventLog\EventLog;
use App\Entity\Idling;
use App\Entity\Importance;
use App\Entity\Invoice;
use App\Entity\Reminder;
use App\Entity\Route;
use App\Entity\ServiceRecord;
use App\Entity\Speeding;
use App\Entity\Team;
use App\Entity\Tracker\TrackerAuth;
use App\Entity\Tracker\TrackerAuthUnknown;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 */
#[ORM\Table(name: 'notification_event')]
#[ORM\UniqueConstraint(columns: ['name', 'type'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\EventRepository')]
class Event extends BaseEntity
{
    public const VEHICLE_CREATED = 'VEHICLE_CREATED';
    public const VEHICLE_DELETED = 'VEHICLE_DELETED';
    public const VEHICLE_UNAVAILABLE = 'VEHICLE_UNAVAILABLE';
    public const VEHICLE_OFFLINE = 'VEHICLE_OFFLINE';
    public const VEHICLE_ONLINE = 'VEHICLE_ONLINE';
    public const VEHICLE_CHANGED_REGNO = 'VEHICLE_CHANGED_REGNO';
    public const VEHICLE_CHANGED_MODEL = 'VEHICLE_CHANGED_MODEL';
    public const VEHICLE_REASSIGNED = 'VEHICLE_REASSIGNED';
    public const VEHICLE_GEOFENCE_ENTER = 'VEHICLE_GEOFENCE_ENTER';
    public const VEHICLE_GEOFENCE_LEAVE = 'VEHICLE_GEOFENCE_LEAVE';

    public const VEHICLE_OVERSPEEDING = 'VEHICLE_OVERSPEEDING';
    public const VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE = 'VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE';
    public const VEHICLE_LONG_STANDING = 'VEHICLE_LONG_STANDING';
    public const VEHICLE_LONG_DRIVING = 'VEHICLE_LONG_DRIVING';
    public const VEHICLE_MOVING = 'VEHICLE_MOVING';
    public const VEHICLE_EXCESSING_IDLING = 'VEHICLE_EXCESSING_IDLING';
    public const VEHICLE_TOWING_EVENT = 'VEHICLE_TOWING_EVENT';
    public const VEHICLE_DRIVING_WITHOUT_DRIVER = 'VEHICLE_DRIVING_WITHOUT_DRIVER';
    public const DRIVER_ROUTE_UNDEFINED = 'DRIVER_ROUTE_UNDEFINED';

    public const SERVICE_REMINDER_SOON = 'SERVICE_REMINDER_SOON';
    public const SERVICE_REMINDER_EXPIRED = 'SERVICE_REMINDER_EXPIRED';
    public const SERVICE_REMINDER_DONE = 'SERVICE_REMINDER_DONE';
    public const SERVICE_REMINDER_DELETED = 'SERVICE_REMINDER_DELETED';
    public const SERVICE_RECORD_ADDED = 'SERVICE_RECORD_ADDED';
    public const SERVICE_REPAIR_ADDED = 'SERVICE_REPAIR_ADDED';

    public const DOCUMENT_EXPIRE_SOON = 'DOCUMENT_EXPIRE_SOON';
    public const DOCUMENT_EXPIRED = 'DOCUMENT_EXPIRED';
    public const DOCUMENT_DELETED = 'DOCUMENT_DELETED';
    public const DOCUMENT_RECORD_ADDED = 'DOCUMENT_RECORD_ADDED';

    public const DRIVER_DOCUMENT_EXPIRE_SOON = 'DRIVER_DOCUMENT_EXPIRE_SOON';
    public const DRIVER_DOCUMENT_EXPIRED = 'DRIVER_DOCUMENT_EXPIRED';
    public const DRIVER_DOCUMENT_DELETED = 'DRIVER_DOCUMENT_DELETED';
    public const DRIVER_DOCUMENT_RECORD_ADDED = 'DRIVER_DOCUMENT_RECORD_ADDED';

    public const ASSET_DOCUMENT_EXPIRE_SOON = 'ASSET_DOCUMENT_EXPIRE_SOON';
    public const ASSET_DOCUMENT_EXPIRED = 'ASSET_DOCUMENT_EXPIRED';
    public const ASSET_DOCUMENT_DELETED = 'ASSET_DOCUMENT_DELETED';
    public const ASSET_DOCUMENT_RECORD_ADDED = 'ASSET_DOCUMENT_RECORD_ADDED';

    public const USER_CREATED = 'USER_CREATED';
    public const USER_CREATED_SYSTEM = 'USER_CREATED_SYSTEM';
    public const USER_BLOCKED = 'USER_BLOCKED';
    public const USER_DELETED = 'USER_DELETED';
    public const USER_PWD_RESET = 'USER_PWD_RESET';
    public const USER_CHANGED_NAME = 'USER_CHANGED_NAME';
    public const USER_CHANGED_SURNAME = 'USER_CHANGED_SURNAME';

    public const ADMIN_USER_CREATED = 'ADMIN_USER_CREATED';
    public const ADMIN_USER_BLOCKED = 'ADMIN_USER_BLOCKED';
    public const ADMIN_USER_DELETED = 'ADMIN_USER_DELETED';
    public const ADMIN_USER_PWD_RESET = 'ADMIN_USER_PWD_RESET';
    public const ADMIN_USER_CHANGED_NAME = 'ADMIN_USER_CHANGED_NAME';

    public const CLIENT_CREATED = 'CLIENT_CREATED';
    public const CLIENT_DEMO_EXPIRED = 'CLIENT_DEMO_EXPIRED';
    public const CLIENT_BLOCKED = 'CLIENT_BLOCKED';
    public const LOGIN_AS_CLIENT = 'LOGIN_AS_CLIENT';
    public const LOGIN_AS_USER = 'LOGIN_AS_USER';

    public const DEVICE_UNKNOWN_DETECTED = 'DEVICE_UNKNOWN_DETECTED';
    public const DEVICE_IN_STOCK = 'DEVICE_IN_STOCK';
    public const DEVICE_OFFLINE = 'DEVICE_OFFLINE';
    public const DEVICE_UNAVAILABLE = 'DEVICE_UNAVAILABLE';
    public const DEVICE_DEACTIVATED = 'DEVICE_DEACTIVATED';
    public const DEVICE_CHANGE_TEAM = 'DEVICE_CHANGE_TEAM';
    public const DEVICE_DELETED = 'DEVICE_DELETED';
    public const DEVICE_REPLACED = 'DEVICE_REPLACED';
    public const TRACKER_VOLTAGE = 'TRACKER_VOLTAGE';
    public const PANIC_BUTTON = 'PANIC_BUTTON';
    public const ODOMETER_CORRECTED = 'ODOMETER_CORRECTED';
    public const DIGITAL_FORM_WITH_FAIL = 'DIGITAL_FORM_WITH_FAIL';
    public const DIGITAL_FORM_IS_NOT_COMPLETED = 'DIGITAL_FORM_IS_NOT_COMPLETED';
    public const TRACKER_JAMMER_STARTED_ALARM = 'TRACKER_JAMMER_STARTED_ALARM';
    public const TRACKER_ACCIDENT_HAPPENED_ALARM = 'TRACKER_ACCIDENT_HAPPENED_ALARM';

    public const SENSOR_TEMPERATURE = 'SENSOR_TEMPERATURE';
    public const SENSOR_HUMIDITY = 'SENSOR_HUMIDITY';
    public const SENSOR_LIGHT = 'SENSOR_LIGHT';
    public const SENSOR_BATTERY_LEVEL = 'SENSOR_BATTERY_LEVEL';
    public const SENSOR_STATUS = 'SENSOR_STATUS';
    public const SENSOR_IO_STATUS = 'SENSOR_IO_STATUS';
    public const DEVICE_SENSOR_WRONG_TEAM = 'DEVICE_SENSOR_WRONG_TEAM'; // @todo implement
    public const DRIVER_ASSIGNMENT_VIOLATION = 'DRIVER_ASSIGNMENT_VIOLATION'; // @todo implement

    public const ASSET_CREATED = 'ASSET_CREATED';
    public const ASSET_DELETED = 'ASSET_DELETED';
    public const ASSET_MISSED = 'ASSET_MISSED';

    public const TRACKER_BATTERY_PERCENTAGE = 'TRACKER_BATTERY_PERCENTAGE';
    public const DEVICE_COMMAND_IS_NOT_APPLIED = 'DEVICE_COMMAND_IS_NOT_APPLIED';

    public const STRIPE_INTEGRATION_ERROR = 'STRIPE_INTEGRATION_ERROR';
    public const STRIPE_PAYMENT_FAILED = 'STRIPE_PAYMENT_FAILED';
    public const STRIPE_PAYMENT_SUCCESSFUL = 'STRIPE_PAYMENT_SUCCESSFUL';

    public const XERO_INTEGRATION_ERROR = 'XERO_INTEGRATION_ERROR';
    public const XERO_INVOICE_CREATION_ERROR = 'XERO_INVOICE_CREATION_ERROR';
    public const XERO_INVOICE_CREATED = 'XERO_INVOICE_CREATED';
    public const XERO_PAYMENT_CREATION_ERROR = 'XERO_PAYMENT_CREATION_ERROR';
    public const XERO_PAYMENT_CREATED = 'XERO_PAYMENT_CREATED';

    public const INVOICE_CREATED = 'INVOICE_CREATED';
    public const PAYMENT_FAILED = 'PAYMENT_FAILED';
    public const PAYMENT_SUCCESSFUL = 'PAYMENT_SUCCESSFUL';

    public const INVOICE_OVERDUE = 'INVOICE_OVERDUE';
    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED = 'INVOICE_OVERDUE_PARTIALLY_BLOCKED';
    public const INVOICE_OVERDUE_BLOCKED = 'INVOICE_OVERDUE_BLOCKED';

    public const INVOICE_CREATED_ADMIN = 'INVOICE_CREATED_ADMIN';
    public const INVOICE_OVERDUE_ADMIN = 'INVOICE_OVERDUE_ADMIN';
    public const INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN = 'INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN';
    public const INVOICE_OVERDUE_BLOCKED_ADMIN = 'INVOICE_OVERDUE_BLOCKED_ADMIN';

    public const DEVICE_CONTRACT_EXPIRED = 'DEVICE_CONTRACT_EXPIRED';

    public const EXCEEDING_SPEED_LIMIT = 'EXCEEDING_SPEED_LIMIT';
    public const INTEGRATION_ENABLED = 'INTEGRATION_ENABLED';

    public const ACCESS_LEVEL_CHANGED = 'ACCESS_LEVEL_CHANGED';

    public const ALLOWED_EVENTS = [
        self::VEHICLE_CREATED,
        self::VEHICLE_DELETED,
        self::VEHICLE_UNAVAILABLE,
        self::VEHICLE_OFFLINE,
        self::VEHICLE_ONLINE,
        self::VEHICLE_CHANGED_REGNO,
        self::VEHICLE_CHANGED_MODEL,
        self::VEHICLE_REASSIGNED,
        self::VEHICLE_GEOFENCE_ENTER,
        self::VEHICLE_GEOFENCE_LEAVE,
        self::VEHICLE_OVERSPEEDING,
        self::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE,
        self::VEHICLE_LONG_STANDING,
        self::VEHICLE_LONG_DRIVING,
        self::VEHICLE_MOVING,
        self::VEHICLE_EXCESSING_IDLING,
        self::VEHICLE_TOWING_EVENT,
        self::VEHICLE_DRIVING_WITHOUT_DRIVER,
        self::DRIVER_ROUTE_UNDEFINED,
        self::SERVICE_REMINDER_SOON,
        self::SERVICE_REMINDER_EXPIRED,
        self::SERVICE_REMINDER_DONE,
        self::SERVICE_REMINDER_DELETED,
        self::SERVICE_RECORD_ADDED,
        self::SERVICE_REPAIR_ADDED,
        self::DOCUMENT_EXPIRE_SOON,
        self::DOCUMENT_EXPIRED,
        self::DOCUMENT_DELETED,
        self::DOCUMENT_RECORD_ADDED,
        self::DRIVER_DOCUMENT_EXPIRE_SOON,
        self::DRIVER_DOCUMENT_EXPIRED,
        self::DRIVER_DOCUMENT_DELETED,
        self::DRIVER_DOCUMENT_RECORD_ADDED,
        self::ASSET_DOCUMENT_EXPIRE_SOON,
        self::ASSET_DOCUMENT_EXPIRED,
        self::ASSET_DOCUMENT_DELETED,
        self::ASSET_DOCUMENT_RECORD_ADDED,
        self::USER_CREATED,
        self::USER_BLOCKED,
        self::USER_DELETED,
        self::USER_PWD_RESET,
        self::USER_CHANGED_NAME,
        self::USER_CHANGED_SURNAME,
        self::ADMIN_USER_CREATED,
        self::ADMIN_USER_BLOCKED,
        self::ADMIN_USER_DELETED,
        self::ADMIN_USER_PWD_RESET,
        self::ADMIN_USER_CHANGED_NAME,
        self::CLIENT_CREATED,
        self::CLIENT_DEMO_EXPIRED,
        self::CLIENT_BLOCKED,
        self::LOGIN_AS_CLIENT,
        self::LOGIN_AS_USER,
        self::DEVICE_UNKNOWN_DETECTED,
        self::DEVICE_IN_STOCK,
        self::DEVICE_OFFLINE,
        self::DEVICE_UNAVAILABLE,
        self::DEVICE_DEACTIVATED,
        self::DEVICE_DELETED,
        self::DEVICE_REPLACED,
        self::TRACKER_VOLTAGE,
        self::PANIC_BUTTON,
        self::ODOMETER_CORRECTED,
        self::DIGITAL_FORM_WITH_FAIL,
        self::DIGITAL_FORM_IS_NOT_COMPLETED,
        self::SENSOR_TEMPERATURE,
        self::SENSOR_HUMIDITY,
        self::SENSOR_LIGHT,
        self::SENSOR_BATTERY_LEVEL,
        self::SENSOR_STATUS,
        self::SENSOR_IO_STATUS,
        self::ASSET_CREATED,
        self::ASSET_DELETED,
        self::ASSET_MISSED,
        self::TRACKER_BATTERY_PERCENTAGE,
        self::STRIPE_INTEGRATION_ERROR,
        self::STRIPE_PAYMENT_FAILED,
        self::STRIPE_PAYMENT_SUCCESSFUL,
        self::XERO_INTEGRATION_ERROR,
        self::XERO_INVOICE_CREATION_ERROR,
        self::XERO_INVOICE_CREATED,
        self::XERO_PAYMENT_CREATION_ERROR,
        self::XERO_PAYMENT_CREATED,
        self::TRACKER_JAMMER_STARTED_ALARM,
        self::TRACKER_ACCIDENT_HAPPENED_ALARM,
    ];

    public const ENTITY_TYPE_USER = User::class;
    public const ENTITY_TYPE_VEHICLE = Vehicle::class;
    public const ENTITY_TYPE_TEAM = Team::class;
    public const ENTITY_TYPE_CLIENT = Client::class;
    public const ENTITY_TYPE_DEVICE = Device::class;
    public const ENTITY_TYPE_REMINDER = Reminder::class;
    public const ENTITY_TYPE_SERVICE_RECORD = ServiceRecord::class;
    public const ENTITY_TYPE_AREA_HISTORY = AreaHistory::class;
    public const ENTITY_TYPE_AREA = Area::class;
    public const ENTITY_TYPE_UNKNOWN_DEVICE_AUTH = TrackerAuthUnknown::class;
    public const ENTITY_TYPE_TRACKER = TrackerAuth::class;
    public const ENTITY_TYPE_TRACKER_HISTORY = TrackerHistory::class;
    public const ENTITY_TYPE_SPEEDING = Speeding::class;
    public const ENTITY_TYPE_ROUTE = Route::class;
    public const ENTITY_TYPE_DOCUMENT = Document::class;
    public const ENTITY_TYPE_DOCUMENT_RECORD = DocumentRecord::class;
    public const ENTITY_TYPE_IDLING = Idling::class;
    public const ENTITY_TYPE_VEHICLE_ODOMETER = VehicleOdometer::class;
    public const ENTITY_TYPE_DIGITAL_FORM_ANSWER = DigitalFormAnswer::class;
    public const ENTITY_TYPE_DIGITAL_FORM = DigitalForm::class;
    public const ENTITY_TYPE_TRACKER_HISTORY_SENSOR = TrackerHistorySensor::class;
    public const ENTITY_TYPE_TRACKER_HISTORY_IO = TrackerHistoryIO::class;
    public const ENTITY_TYPE_ASSET = Asset::class;
    public const ENTITY_TYPE_INVOICE = Invoice::class;

    public const TYPE_SYSTEM = 'system';
    public const TYPE_USER = 'user';

    public const ADDITIONAL_SETTING = 'additionalSettings';

    public const ADDITIONAL_SETTING_HEADER_COLUMNS = 'headersColumns';
    public const SETTING_EVENT_LOG = 'eventLog';
    public const ADDITIONAL_SETTING_MAPPING_FILTERS = 'mappingFilters';
    public const ADDITIONAL_SETTING_IS_DEVICE_VOLTAGE = 'isDeviceVoltage';
    public const ADDITIONAL_SETTING_IS_OVER_SPEEDING = 'isOverSpeed';
    public const ADDITIONAL_SETTING_IS_TIME_DURATION = 'isTimeDuration';
    public const ADDITIONAL_SETTING_IS_LISTENER_TEAM = 'isListenerTeam';
    public const ADDITIONAL_SETTING_IS_DISTANCE = 'isDistance';
    public const ADDITIONAL_SETTING_IS_DRIVER_TO_RECIPIENT = 'isDriverToRecipient';
    public const ADDITIONAL_SETTING_IS_SENSOR_TEMPERATURE = 'isSensorTemperature';
    public const ADDITIONAL_SETTING_IS_SENSOR_HUMIDITY = 'isSensorHumidity';
    public const ADDITIONAL_SETTING_IS_SENSOR_LIGHT = 'isSensorLight';
    public const ADDITIONAL_SETTING_IS_SENSOR_BATTERY_LEVEL = 'isBatteryLevel';
    public const ADDITIONAL_SETTING_IS_SENSOR_STATUS = 'isSensorStatus';
    public const ADDITIONAL_SETTING_IS_SENSOR_IO_STATUS = 'isSensorIOStatus';
    public const ADDITIONAL_SETTING_IS_SENSOR_IO_TYPE = 'isSensorIOType';
    public const ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE = 'isAreaTriggerType';
    public const ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR = 'isExpressionOperator';
    public const ADDITIONAL_SETTING_IS_DEVICE_BATTERY = 'isDeviceBatteryPercentage';
    public const ADDITIONAL_SETTING_IS_THRESHOLD_SPEED_LIMIT = 'isThresholdSpeedLimit';

    public const ALLOWED_TYPES = [
        self::TYPE_SYSTEM,
        self::TYPE_USER,
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'alias',
        'eventSource',
        'scopes',
        'additionalScopes',
        'importance',
        'additionalSettings',
        'triggeredBy'
    ];

    public const BY_USER = 'user';
    public const BY_GEO = 'geo';
    public const BY_DRIVER = 'driver';
    public const BY_SENSOR = 'sensor';

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('alias', $include, true)) {
            $data['alias'] = $this->getAlias();
        }

        if (in_array('eventSource', $include, true)) {
            $data['eventSource'] = $this->getSourceTypeByEntity();
        }

        if (in_array('entity', $include, true)) {
            $data['entity'] = $this->getEntity();
        }

        if (in_array('scopes', $include, true)) {
            $data['scopes'] = $this->getScopeTypesArray(ScopeType::GENERAL_SCOPE_CATEGORY);
        }

        if (in_array('additionalScopes', $include, true)) {
            $data['additionalScopes'] = $this->getScopeTypesArray(ScopeType::ADDITIONAL_SCOPE_CATEGORY);
        }

        if (in_array('importance', $include, true)) {
            $data['importance'] = $this->getImportanceName();
        }

        if (in_array('notifications', $include, true)) {
            $data['notifications'] = $this->getNotifications();
        }

        if (in_array('additionalSettings', $include, true)) {
            $data['additionalSettings'] = $this->getAdditionalSettings() ? $this->getAdditionalSettings() : null;
        }

        if (in_array('triggeredBy', $include, true)) {
            $data['triggeredBy'] = $this->getTriggeredBy();
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
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'alias', type: 'string', length: 255)]
    private $alias;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    /**
     * @var string
     */
    #[ORM\Column(name: 'entity', type: 'string', length: 255)]
    private $entity;

    /**
     * @var ArrayCollection|ScopeType[]
     */
    #[ORM\JoinTable(name: 'notification_events2scopes_types')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'scope_type_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'ScopeType')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $scopeTypes;

    /**
     * @var Importance
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Importance')]
    #[ORM\JoinColumn(name: 'importance_id', referencedColumnName: 'id')]
    private $importance;

    /**
     * @var ArrayCollection|Notification[]
     */
    #[ORM\JoinTable(name: 'notification')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[ORM\OneToMany(targetEntity: 'App\Entity\Notification\Notification', mappedBy: 'event', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $notifications;

    /**
     * @var array
     */
    #[ORM\Column(name: 'additional_settings', type: 'json', nullable: true)]
    private $additionalSettings;

    /**
     * @var string
     */
    #[ORM\Column(name: 'triggered_by', type: 'string', length: 255, nullable: true)]
    private $triggeredBy;

    public function __construct()
    {
        $this->scopeTypes = new ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alias.
     *
     * @param string $alias
     *
     * @return Event
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Event
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set entity.
     *
     * @param string $entity
     *
     * @return Event
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get entity.
     *
     * @return string
     */
    public function getStringEntity()
    {
        return StringHelper::getClassName($this->getEntity());
    }

    /**
     * Get entity.
     *
     * @return string
     */
    public function getRealEntity()
    {
        return ClassUtils::getRealClass($this->getEntity());
    }

    /**
     * @return ScopeType[]|ArrayCollection
     */
    public function getScopeTypes()
    {
        return $this->scopeTypes;
    }

    /**
     * @param string $category
     * @return array
     */
    public function getScopeTypesArray(string $category): array
    {
        return $this->getScopeTypeByCategory($category)->map(
            static function (ScopeType $s) {
                return $s->toArray(['name', 'subType']);
            }
        )->getValues();
    }

    /**
     * @param string $subtype
     * @param string $category
     * @return ScopeType|null
     */
    public function getScopeType(string $subtype, string $category): ?ScopeType
    {
        return $this
            ->getScopeTypes()
            ->filter(
                static function (ScopeType $v) use ($subtype, $category) {
                    return ($v->getCategory() === $category) && ($v->getSubType() === $subtype);
                }
            )->first();
    }

    /**
     * @param string $category
     * @return ArrayCollection
     */
    public function getScopeTypeByCategory(string $category)
    {
        return $this
            ->getScopeTypes()
            ->filter(
                static function (ScopeType $v) use ($category) {
                    return $v->getCategory() === $category;
                }
            );
    }

    /**
     * @param string $category
     * @param string $subtype
     * @return bool
     */
    public function scopeTypeAllowed(string $category, string $subtype): bool
    {
        return $this->getScopeTypes()->exists(
            static function ($k, ScopeType $st) use ($subtype, $category) {
                return ($st->getCategory() === $category) && ($st->getSubType() === $subtype);
            }
        );
    }

    /**
     * @param ScopeType[]|ArrayCollection $scopeTypes
     * @return $this
     */
    public function setScopeTypes($scopeTypes)
    {
        $this->scopeTypes = $scopeTypes;

        return $this;
    }

    /**
     * @param ScopeType $scopeType
     * @return $this
     */
    public function addScopeType(ScopeType $scopeType)
    {
        $this->scopeTypes->add($scopeType);

        return $this;
    }

    /**
     * @param ScopeType $scopeType
     */
    public function removeScopeType(ScopeType $scopeType)
    {
        $this->scopeTypes->removeElement($scopeType);
    }

    /**
     * @param Importance $importance
     * @return $this
     */
    public function setImportance(Importance $importance)
    {
        $this->importance = $importance;

        return $this;
    }

    /**
     * @return Importance
     */
    public function getImportance(): Importance
    {
        return $this->importance;
    }


    public function getImportanceName()
    {
        return $this->importance ? $this->importance->getName() : null;
    }

    /**
     * @return ArrayCollection|Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications->matching(
            Criteria::create()->where(Criteria::expr()->in('status',
                [Notification::STATUS_ENABLED, Notification::STATUS_DISABLED]))
        );
    }

    /**
     * @return array
     */
    public function getNotificationArray(): array
    {
        return array_values(
            $this->getNotifications()->map(
                static function (Notification $notification) {
                    return $notification->toArray(['id', 'status', 'ownerTeamId']);
                }
            )->toArray()
        );
    }

    /**
     * @return int
     */
    public function getNotificationCount(): int
    {
        return $this->getNotifications()->count();
    }

    /**
     * @return array|null
     */
    public function getAdditionalSettings(): ?array
    {
        return $this->additionalSettings;
    }

    /**
     * @param $additionalSettings
     * @return $this
     */
    public function setAdditionalSettings($additionalSettings)
    {
        $this->additionalSettings = array_merge($this->additionalSettings ?? [], $additionalSettings ?? []);

        return $this;
    }

    public function isSensorTemperature(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_TEMPERATURE] ?? false;
    }

    public function isSensorHumidity(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_HUMIDITY] ?? false;
    }

    public function isSensorLight(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_LIGHT] ?? false;
    }

    public function isSensorBatteryLevel(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_BATTERY_LEVEL] ?? false;
    }

    public function isSensorStatus(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_STATUS] ?? false;
    }

    public function isSensorIOStatus(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_IO_STATUS] ?? false;
    }

    public function isSensorIOType(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_SENSOR_IO_TYPE] ?? false;
    }

    /**
     * @return bool
     */
    public function isDeviceVoltage(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_DEVICE_VOLTAGE] ?? false;
    }

    public function isDeviceBattery(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_DEVICE_BATTERY] ?? false;
    }

    /**
     * @return bool
     */
    public function isOverSpeed(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_OVER_SPEEDING] ?? false;
    }

    /**
     * @return bool
     */
    public function isTimeDuration(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_TIME_DURATION] ?? false;
    }

    /**
     * @return bool
     */
    public function isListenerTeam(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_LISTENER_TEAM] ?? true;
    }

    /**
     * @return bool
     */
    public function isDistance(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_DISTANCE] ?? false;
    }

    /**
     * @return bool
     */
    public function isDriverToRecipient(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_DRIVER_TO_RECIPIENT] ?? false;
    }

    /**
     * @return bool
     */
    public function isTriggerInCertainAreas(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE] ?? false;
    }

    /**
     * @return bool
     */
    public function isExpressionOperator(): bool
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR] ?? false;
    }

    /**
     * @return array|null
     */
    public function getHeaderByEvent(): ?array
    {
        return $this->getAdditionalSettings()[self::ADDITIONAL_SETTING_HEADER_COLUMNS] ?? null;
    }

    /**
     * @param $header
     * @return $this
     */
    public function setHeaderByEvent($header)
    {
        $data[self::ADDITIONAL_SETTING_HEADER_COLUMNS] = $header;

        return $this->setAdditionalSettings($data);
    }

    /**
     * @return array|null
     */
    public function getMappingFiltersByEvent(): ?array
    {
        return
            $this->getAdditionalSettings()[self::SETTING_EVENT_LOG][self::ADDITIONAL_SETTING_MAPPING_FILTERS] ?? null;
    }

    /**
     * @return string
     */
    private function getSourceTypeByEntity()
    {
        $entity = $this->getEntity();

        switch ($entity) {
            case Event::ENTITY_TYPE_AREA_HISTORY:
            case Event::ENTITY_TYPE_SPEEDING:
            case Event::ENTITY_TYPE_ROUTE:
            case Event::ENTITY_TYPE_IDLING:
            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                return StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE);
            case Event::ENTITY_TYPE_UNKNOWN_DEVICE_AUTH:
            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                return StringHelper::getClassName(Event::ENTITY_TYPE_DEVICE);
            case Event::ENTITY_TYPE_DOCUMENT_RECORD:
                return StringHelper::getClassName(Event::ENTITY_TYPE_DOCUMENT);
            case Event::ENTITY_TYPE_SERVICE_RECORD:
                return StringHelper::getClassName(Event::ENTITY_TYPE_REMINDER);
            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                return StringHelper::getClassName(Event::ENTITY_TYPE_DIGITAL_FORM);
            default:
                return StringHelper::getClassName($entity);
        }
    }

    /**
     * Set triggeredBy.
     *
     * @param string $triggeredBy
     *
     * @return Event
     */
    public function setTriggeredBy($triggeredBy = null)
    {
        $this->triggeredBy = $triggeredBy;

        return $this;
    }

    /**
     * Get triggeredBy.
     *
     * @return string
     */
    public function getTriggeredBy()
    {
        return $this->triggeredBy;
    }


    public function getAdditionalExportFields(): array
    {
        $fields = [];

        if (in_array($this->getName(), [
            Event::VEHICLE_OVERSPEEDING,
            Event::VEHICLE_MOVING,
            Event::VEHICLE_LONG_STANDING,
            Event::VEHICLE_LONG_DRIVING,
            Event::VEHICLE_TOWING_EVENT
        ])) {
            $fields[] = EventLog::ADDRESS;
        }

        return $fields;
    }
}
