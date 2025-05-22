<?php

namespace App\Entity\EventLog;

use App\Entity\Acknowledge;
use App\Entity\Notification\Event;
use App\Entity\BaseEntity;
use App\Entity\Notification\Message;
use App\Entity\Team;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventLog
 */
#[ORM\Table(name: 'event_log')]
#[ORM\Index(columns: ['detail_id', 'event_id'], name: 'event_log_detail_id_index')]
#[ORM\Index(columns: ['entity_id', 'event_id'], name: 'event_log_entity_id_index')]
#[ORM\Index(columns: ['vehicle_id', 'event_id'], name: 'event_log_vehicle_id_index')]
#[ORM\Index(columns: ['event_id', 'event_date', 'entity_team_id'], name: 'event_log_event_id_event_date_entity_team_id_index')]
#[ORM\Entity(repositoryClass: 'App\Repository\EventLog\EventLogRepository')]
class EventLog extends BaseEntity
{
    public const EVENT_LOG_ID = 'id';
    public const DATE = 'formattedDate';
    public const IMPORTANCE = 'importance';
    public const EVENT_ID = 'eventId';
    public const EVENT_TEAM = 'eventTeam';
    public const EVENT_TYPE = 'eventType';
    public const EVENT_SOURCE = 'eventSource';
    public const EVENT_SOURCE_TYPE = 'eventSourceType';
    public const TRIGGERED_DETAILS = 'triggeredDetails';
    public const NTF_GENERATED = 'notificationGenerated';
    public const NTF_LIST = 'notificationsList';
    public const OLD_VALUE = 'oldValue';
    public const NEW_VALUE = 'newValue';

    public const SHORT_DETAILS = 'shortDetails';
    public const CONTEXT = 'context';
    public const VEHICLE_REG_NO = 'vehicleRegNo';
    public const VEHICLE_DEFAULT_LABEL = 'defaultLabel';
    public const TITLE = 'title';
    public const ADDRESS = 'address';
    public const LAT = 'lat';
    public const LNG = 'lng';
    public const AREAS = 'areas';
    public const DEVICE_VOLTAGE = 'deviceVoltage';
    public const DEVICE_BATTERY_PERCENTAGE = 'deviceBatteryPercentage';
    public const EXPIRED_DATE = 'expiredDate';
    public const LAST_COORDINATES = 'lastCoordinates';
    public const DEVICE_IMEI = 'deviceImei';
    public const DEVICE_ID = 'deviceId';
    public const DURATION = 'duration';
    public const DISTANCE = 'distance';
    public const MAX_SPEED = 'maxSpeed';
    public const SPEED = 'speed';
    public const LIMIT = 'limit';
    public const USER = 'user';
    public const FORM = 'form';
    public const SPEED_LIMIT = 'speedLimit';
    public const SPEED_OVER_LIMIT_PERCENT = 'speedOverLimitPercent';

    public const SENSOR_TEMPERATURE = 'sensorTemperature';
    public const SENSOR_HUMIDITY = 'sensorHumidity';
    public const SENSOR_LIGHT = 'sensorLight';
    public const SENSOR_BATTERY_LEVEL = 'sensorBatteryLevel';
    public const SENSOR_STATUS = 'sensorStatus';
    public const SENSOR_IO_TYPE = 'sensorIoType';

    /* Invoice entity */
    public const ERROR_MSG = 'errorMsg';
    public const EXT_INVOICE_ID = 'extInvoiceId';
    public const ERRORS = 'errors';

    public const DISPLAYED_VALUES = [
        'id',
        'importance',
        'triggeredBy',
        'triggeredDetails',
        'entityId',
        'event',
        'eventName',
        'eventSourceType',
        'eventSource',
        'eventTeam',
        'notificationGenerated',
        'notificationsList',
        'formattedDate',
        'teamId',
        'createdAt',
        'triggeredBy',
        'details',
        'triggeredByDetails',
        'acknowledge'
    ];

    public const DEFAULT_EXPORT_VALUES = [
        EventLog::EVENT_LOG_ID => 'headers.general.id',
        EventLog::EVENT_TEAM => 'headers.general.eventTeam',
        EventLog::EVENT_TYPE => 'headers.general.eventType',
        EventLog::DATE => 'headers.general.formattedDate',
        EventLog::IMPORTANCE => 'headers.general.importance',
        EventLog::TRIGGERED_DETAILS => 'headers.general.triggeredDetails',
        EventLog::EVENT_SOURCE_TYPE => 'headers.general.eventSourceType',
        EventLog::EVENT_SOURCE => 'headers.general.eventSource',
        EventLog::NTF_LIST => 'headers.general.notificationsList',
    ];

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->acknowledge = new ArrayCollection();
    }

    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->id;
        }

        if (in_array('event', $include, true)) {
            $data['event'] = $this->getEventArray();
        }

        if (in_array('eventName', $include, true)) {
            $data['eventName'] = $this->getEvent()->getName();
        }

        if (in_array('importance', $include, true)) {
            $data['importance'] = $this->getEvent()->getImportanceName();
        }

        if (in_array('entityId', $include, true)) {
            $data['entityId'] = $this->getRealEntityId();
        }

        if (in_array('triggeredBy', $include, true)) {
            $data['triggeredBy'] = $this->getTriggeredBy();
        }

        if (in_array('triggeredDetails', $include, true)) {
            $data['triggeredDetails'] = $this->getTriggeredDetails();
        }

        if (in_array('eventSourceType', $include, true)) {
            $data['eventSourceType'] = $this->getEventSourceType();
        }

        if (in_array('eventDetails', $include, true)) {
            $data['eventDetails'] = $this->getEventDetails();
        }

        if (in_array('eventSource', $include, true)) {
            $data['eventSource'] = $this->getEventSource();
        }

        if (in_array('eventTeam', $include, true)) {
            $data['eventTeam'] = $this->getEventTeam();
        }

        if (in_array('notificationGenerated', $include, true)) {
            $data['notificationGenerated'] = $this->getNotificationGenerated();
        }

        if (in_array('notificationsList', $include, true)) {
            $data['notificationsList'] = $this->getNotificationsList();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        if (in_array('formattedDate', $include, true)) {
            $data['formattedDate'] = $this->getFormattedDate();
        }

        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->getId();
        }

        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeamId();
        }

        if (in_array('details', $include, true)) {
            $data['details'] = $this->getDetails() ? $this->getDetails() : null;
        }

        if (in_array('triggeredByDetails', $include, true)) {
            $data['triggeredByDetails'] = $this->getTriggeredByDetails();
        }

        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }

        if (in_array('acknowledge', $include, true)) {
            $data['acknowledge'] = $this->getAcknowledgeArray();
        }

        if (in_array('address', $include, true)) {
            $data['address'] = $this->getDetails()['context']['address'] ?? null;
        }

        if (in_array('imei', $include, true)) {
            $data['imei'] = $this->getDetails()['device']['imei'] ?? null;
        }

        return $data;
    }

    public function toExport(array $include = []): array
    {
        return $this->toArray($include);
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Event
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Event')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private $event;

    /**
     * @var string
     */
    #[ORM\Column(name: 'triggered_details', type: 'string', length: 255, nullable: true)]
    private $triggeredDetails;

    /**
     * @var array|null
     */
    #[ORM\Column(name: 'triggered_by_details', type: 'json', nullable: true)]
    private $triggeredByDetails;

    /**
     * @var string
     */
    #[ORM\Column(name: 'event_source_type', type: 'string', length: 255, nullable: true)]
    private $eventSourceType;

    /**
     * @var array|null
     */
    #[ORM\Column(name: 'event_details', type: 'json', nullable: true)]
    private $eventDetails;

    /**
     * @var array
     */
    #[ORM\Column(name: 'details', type: 'json', nullable: false, options: ['jsonb' => true])]
    private $details;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'vehicle_id', type: 'integer', nullable: true)]
    private $vehicleId;

    /**
     * @var array|null
     */
    #[ORM\Column(name: 'notifications_list', type: 'json', nullable: true)]
    private $notificationsList;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'event_date', type: 'datetime', nullable: true)]
    private $eventDate;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'detail_id', type: 'integer', nullable: true)]
    private $detailId;

    /**
     * @var Acknowledge
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Acknowledge', mappedBy: 'eventLog', fetch: 'EXTRA_LAZY')]
    private $acknowledge;

    /**
     * @var Message
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Notification\Message', mappedBy: 'eventLog', fetch: 'EXTRA_LAZY')]
    private $message;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'device_id', type: 'integer', nullable: true)]
    private ?int $deviceId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'driver_id', type: 'integer', nullable: true)]
    private ?int $driverId;


    #[ORM\Column(name: 'short_details', type: 'json', nullable: true, options: ['jsonb' => true])]
    private $shortDetails;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'entity_id', type: 'bigint', nullable: true)]
    private ?int $entityId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'entity_team_id', type: 'integer', nullable: true)]
    private ?int $entityTeamId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'team_by', type: 'integer', nullable: true)]
    private ?int $teamBy;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'user_by', type: 'integer', nullable: true)]
    private ?int $userBy;

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
     * @param Event|object $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getEventArray(): array
    {
        return $this->event ? $this->event->toArray(['id', 'name', 'eventSource', 'entity', 'alias']) : [];
    }

    /**
     * Set details.
     *
     * @param array $details
     *
     * @return EventLog
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Get Entity Id.
     *
     * @return mixed|null
     */
    public function getRealEntityId()
    {
        switch ($this->getEvent()->getRealEntity()) {
            case Event::ENTITY_TYPE_AREA_HISTORY:
                return $this->details['vehicle']['id'] ?? null;
            default:
                return $this->details['id'] ?? null;
        }
    }

    /**
     * Set notificationsList.
     *
     * @param array|null $notificationsList
     *
     * @return EventLog
     */
    public function setNotificationsList($notificationsList = null)
    {
        $this->notificationsList = $notificationsList;

        return $this;
    }

    /**
     * Get notificationsList.
     *
     * @return array|null
     */
    public function getNotificationsList()
    {
        return $this->notificationsList;
    }

    public function getNotificationGenerated()
    {
        return !empty($this->notificationsList) ? 'yes' : 'no';
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return EventLog
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function createdAt()
    {
        $this->createdAt = new \DateTime();
    }

    public function getFormattedDate()
    {
        return $this->getEventDate()
            ? $this->formatDate($this->getEventDate())
            : null;
    }

    /**
     * @return string
     */
    public function getTriggeredBy()
    {
        return $this->getEvent()->getTriggeredBy();
    }

    /**
     * Set triggeredDetails.
     *
     * @param string $triggeredDetails
     *
     * @return EventLog
     */
    public function setTriggeredDetails($triggeredDetails)
    {
        $this->triggeredDetails = $triggeredDetails;

        return $this;
    }

    /**
     * Get triggeredDetails.
     *
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->triggeredDetails;
    }

    /**
     * Set eventSourceType.
     *
     * @param string $eventSourceType
     *
     * @return EventLog
     */
    public function setEventSourceType($eventSourceType)
    {
        $this->eventSourceType = $eventSourceType;

        return $this;
    }

    /**
     * Get eventSourceType.
     *
     * @return string
     */
    public function getEventSourceType()
    {
        return $this->eventSourceType;
    }

    /**
     * Set eventDetails.
     *
     * @param array|null $eventDetails
     *
     * @return EventLog
     */
    public function setEventDetails($eventDetails)
    {
        $this->eventDetails = $eventDetails;

        return $this;
    }

    /**
     * Get eventDetails.
     *
     * @return array|null
     */
    public function getEventDetails()
    {
        return $this->eventDetails;
    }

    /**
     * Set triggeredByDetails.
     *
     * @param array|null $triggeredByDetails
     *
     * @return EventLog
     */
    public function setTriggeredByDetails($triggeredByDetails)
    {
        $this->triggeredByDetails = $triggeredByDetails;

        return $this;
    }

    /**
     * Get triggeredByDetails.
     *
     * @return array|null
     */
    public function getTriggeredByDetails()
    {
        return $this->triggeredByDetails;
    }


    /**
     * Get eventSource.
     *
     * @return array|null
     */
    public function getEventSource()
    {
        return $this->eventDetails['eventSource'];
    }

    /**
     * Get eventTeam.
     *
     * @return array|null
     */
    public function getEventTeam()
    {
        return $this->eventDetails['eventTeam'];
    }

    /**
     * Set createdBy.
     *
     * @param User|null $createdBy
     * @return $this
     */
    public function setCreatedBy(?User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @return int|null
     */
    public function getTeamId()
    {
        if ($this->getTeam()) {
            return $this->getTeam()->getId();
        } else {
            return $this->getCreatedBy() ? $this->getCreatedBy()->getTeam()->getId() : null;
        }
    }

    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @param null $date
     * @return $this
     */
    public function setEventDate($date = null)
    {
        $this->eventDate = $date;

        return $this;
    }

    /**
     * Get team
     *
     * @return Team
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return EventLog
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setDetailId($id)
    {
        $this->detailId = (int)$id ? (int)$id : null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDetailId(): ?int
    {
        return $this->detailId;
    }

    /**
     * @return ArrayCollection
     */
    public function getAcknowledge()
    {
        return $this->acknowledge;
    }

    public function getAcknowledgeArray(array $fields = ['status', 'comment', 'recipients'])
    {
        if ($this->getAcknowledge()->first()) {
            return $this->getAcknowledge()->first()->toArray($fields);
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    /**
     * @param int|null $vehicleId
     * @return $this
     */
    public function setVehicleId(?int $vehicleId)
    {
        $this->vehicleId = $vehicleId ?? null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDeviceId(): ?int
    {
        return $this->deviceId;
    }

    /**
     * @param int|null $deviceId
     * @return $this
     */
    public function setDeviceId(?int $deviceId)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->driverId;
    }

    /**
     * @param int|null $driverId
     * @return $this
     */
    public function setDriverId(?int $driverId)
    {
        $this->driverId = $driverId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortDetails(): mixed
    {
        return $this->shortDetails;
    }

    /**
     * @param array|null $shortDetails
     * @return $this
     */
    public function setShortDetails(?array $shortDetails)
    {
        $this->shortDetails = $shortDetails;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * @param int|null $entityId
     * @return $this
     */
    public function setEntityId(?int $entityId)
    {
        $this->entityId = (int)$entityId ?? null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entityTeamId;
    }

    /**
     * @param int|null $entityTeamId
     * @return $this
     */
    public function setEntityTeamId(?int $entityTeamId)
    {
        $this->entityTeamId = $entityTeamId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTeamBy(): ?int
    {
        return $this->teamBy;
    }

    /**
     * @param int|null $teamBy
     * @return $this
     */
    public function setTeamBy(?int $teamBy)
    {
        $this->teamBy = $teamBy;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserBy(): ?int
    {
        return $this->userBy;
    }

    /**
     * @param int|null $userBy
     * @return $this
     */
    public function setUserBy(?int $userBy)
    {
        $this->userBy = $userBy;

        return $this;
    }
}
