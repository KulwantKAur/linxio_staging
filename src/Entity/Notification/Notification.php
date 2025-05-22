<?php

namespace App\Entity\Notification;

use App\Entity\Acknowledge;
use App\Entity\BaseEntity;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Notification
 */
#[ORM\Table(name: 'notification')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\NotificationRepository')]
#[ORM\HasLifecycleCallbacks]
class Notification extends BaseEntity
{
    public const ALLOWED_STATUSES = [
        self::STATUS_ENABLED,
        self::STATUS_DISABLED,
    ];
    public const ALL_STATUSES = [
        self::STATUS_ENABLED,
        self::STATUS_DISABLED,
        self::STATUS_DELETED
    ];

    public const TYPE_IMPORTANCE_IMMEDIATELY = 'immediately';
    public const TYPE_IMPORTANCE_BUSINESS_HOURS = 'business_hours';
    public const TYPE_IMPORTANCE_CUSTOM = 'custom';

    public const ALLOWED_IMPORTANCE_TYPES = [
        self::TYPE_IMPORTANCE_IMMEDIATELY,
        self::TYPE_IMPORTANCE_BUSINESS_HOURS,
        self::TYPE_IMPORTANCE_CUSTOM,
    ];

    public const DEFAULT_EVENT_TRACKING_TIME_FROM = "00:00";
    public const DEFAULT_EVENT_TRACKING_TIME_UNTIL = "23:59";

    public const MONDAY = "monday";
    public const TUESDAY = "tuesday";
    public const WEDNESDAY = "wednesday";
    public const THURSDAY = "thursday";
    public const FRIDAY = "friday";
    public const SATURDAY = "saturday";
    public const SUNDAY = "sunday";

    public const ALL_EVENT_TRACKING_DAYS = [
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
        self::SATURDAY,
        self::SUNDAY,
    ];

    public const DEFAULT_EVENT_TRACKING_DAYS = [
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
    ];

    public const DEFAULT_DEVICE_VOLTAGE_LIMIT = 3700;
    public const DEFAULT_OVERSPEEDING = 120;
    public const DEFAULT_LONG_STANDING_DURATION = 600;
    public const DEFAULT_OVERSPEEDING_DURATION = 1;

    public const ADDITIONAL_PARAMS = 'additionalParams';

    public const TIME_DURATION = 'timeDuration';
    public const THRESHOLD_SPEED_LIMIT = 'thresholdSpeedLimit';
    public const OVER_SPEED = 'overSpeed';
    public const DEVICE_VOLTAGE = 'deviceVoltage';
    public const DISTANCE = 'distance';
    public const DEVICE_BATTERY_PERCENTAGE = 'deviceBatteryPercentage';
    public const TYPE = 'type';
    public const FROM = 'from';
    public const TO = 'to';
    public const TEMPERATURE = 'temperature';
    public const HUMIDITY = 'humidity';
    public const LIGHT = 'light';
    public const BATTERY_LEVEL = 'batteryLevel';
    public const STATUS = 'status';
    public const STATUS_IO = 'statusIO';
    public const SENSOR_IO_TYPE_ID = 'sensorIOTypeId';
    public const AREA_TRIGGER_TYPE = 'areaTriggerType';
    public const AREA_TRIGGER_TYPE_EVERYWHERE = 'everywhere';
    public const AREA_TRIGGER_TYPE_INSIDE = 'inside';
    public const AREA_TRIGGER_TYPE_OUTSIDE = 'outside';

    public const SENSOR_TYPE_GREATER = 'greater';
    public const SENSOR_TYPE_LESS = 'less';
    public const SENSOR_TYPE_OUTSIDE = 'outside';

    public const EXPRESSION_OPERATOR = 'exprOperator';
    public const OPERATOR_AND = 'and';
    public const OPERATOR_OR = 'or';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'title',
        'status',
        'importance',
        'eventId',
        'listenerTeamId',
        'ownerTeamId',
        'scope',
        'additionalScope',
        'recipients',
        'acknowledgeRecipients',
        'transports',
        'createdAt',
        'createdById',
        'updatedAt',
        'updatedById',
        'eventTrackingTimeFrom',
        'eventTrackingTimeUntil',
        'eventTrackingDays',
        'comment',
        'listenerTeam',
        'additionalParams',
        'sendTimeFrom',
        'sendTimeUntil',
    ];

    public const DEFAULT_LISTING_DISPLAY_VALUES = [
        'id',
        'title',
        'status',
        'importance',
        'event',
        'listenerTeamId',
        'ownerTeamId',
        'scope',
        'recipients',
        'acknowledgeRecipients',
        'transports',
        'createdAt',
        'createdById',
        'updatedAt',
        'updatedById'
    ];

    public const NOTIFICATION_PLACEHOLDERS = [
        'comment',
        self::OVER_SPEED,
        self::TIME_DURATION,
        self::DEVICE_VOLTAGE,
        self::DEVICE_BATTERY_PERCENTAGE,
        self::TYPE,
        self::FROM,
        self::TO,
        self::TEMPERATURE,
        self::HUMIDITY,
        self::LIGHT,
        self::BATTERY_LEVEL,
        self::STATUS,
        self::STATUS_IO,
        self::SENSOR_IO_TYPE_ID,
        self::AREA_TRIGGER_TYPE,
    ];

    public function toArray(array $include = [], ?TranslatorInterface $translator = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->id;
        }

        if (in_array('title', $include, true)) {
            $data['title'] = $this->title;
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->status;
        }

        if (in_array('importance', $include, true)) {
            $data['importance'] = $this->importance;
        }

        if (in_array('eventId', $include, true)) {
            $data['eventId'] = $this->event->getId();
        }

        if (in_array('event', $include, true)) {
            $data['event'] = $this->event->toArray(['name']);
        }

        if (in_array('scope', $include, true)) {
            $data['scope'] = $this->getScopesByCategory(ScopeType::GENERAL_SCOPE_CATEGORY);
        }

        if (in_array('additionalScope', $include, true)) {
            $data['additionalScope'] = $this->getScopesByCategory(ScopeType::ADDITIONAL_SCOPE_CATEGORY);
        }

        if (in_array('recipients', $include, true)) {
            $data['recipients'] = $this->getRecipientsArray();
        }

        if (in_array('acknowledgeRecipients', $include, true)) {
            $data['acknowledgeRecipients'] = $this->getAcknowledgeRecipientsArray();
        }

        if (in_array('transports', $include, true)) {
            $data['transports'] = $this->getTransportsArray();
        }

        if (in_array('ownerTeamId', $include, true)) {
            $data['ownerTeamId'] = $this->getOwnerTeamId();
        }

        if (in_array('ownerTeam', $include, true)) {
            $data['ownerTeam'] = $this->getOwnerTeamArray();
        }

        if (in_array('listenerTeamId', $include, true)) {
            $data['listenerTeamId'] = $this->getListenerTeamId();
        }

        if (in_array('listenerTeam', $include, true)) {
            $data['listenerTeam'] = $this->getListenerTeamArray();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }

        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedByArray();
        }

        if (in_array('createdById', $include, true)) {
            $data['createdById'] = $this->getCreatedById();
        }

        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->updatedAt);
        }

        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByArray();
        }

        if (in_array('updatedById', $include, true)) {
            $data['updatedById'] = $this->getUpdatedById();
        }

        if (in_array('eventTrackingTimeFrom', $include, true)) {
            $data['eventTrackingTimeFrom'] = $this->getEventTrackingTimeFrom();
        }

        if (in_array('eventTrackingTimeUntil', $include, true)) {
            $data['eventTrackingTimeUntil'] = $this->getEventTrackingTimeUntil();
        }

        if (in_array('sendTimeFrom', $include, true)) {
            $data['sendTimeFrom'] = $this->getSendTimeFrom();
        }

        if (in_array('sendTimeUntil', $include, true)) {
            $data['sendTimeUntil'] = $this->getSendTimeUntil();
        }

        if (in_array('eventTrackingDays', $include, true)) {
            $data['eventTrackingDays'] = $this->getEventTrackingDays();
        }

        if (in_array('comment', $include, true)) {
            $data['comment'] = $this->getComment();
        }

        if (in_array('additionalParams', $include, true)) {
            $data['additionalParams'] = $this->getAdditionalParams();
        }

        if (in_array(self::OVER_SPEED, $include, true)) {
            $data[self::OVER_SPEED] = $this->getAdditionalParams()[self::OVER_SPEED] ?? null;
        }

        if (in_array(self::TIME_DURATION, $include, true)) {
            $data[self::TIME_DURATION] = $this->getAdditionalParams()[self::TIME_DURATION] ?? null;
        }

        if (in_array(self::DEVICE_VOLTAGE, $include, true)) {
            $data[self::DEVICE_VOLTAGE] = $this->getAdditionalParams()[self::DEVICE_VOLTAGE] ?? null;
        }

        if (in_array(self::TYPE, $include, true)) {
            $data[self::TYPE] = $this->getAdditionalParams()[self::TYPE] ?? null;
        }

        if (in_array(self::FROM, $include, true)) {
            $data[self::FROM] = $this->getAdditionalParams()[self::FROM] ?? null;
        }

        if (in_array(self::TO, $include, true)) {
            $data[self::TO] = $this->getAdditionalParams()[self::TO] ?? null;
        }

        if (in_array(self::DEVICE_BATTERY_PERCENTAGE, $include, true)) {
            $data[self::DEVICE_BATTERY_PERCENTAGE] = $this->getAdditionalParams()[self::DEVICE_BATTERY_PERCENTAGE] ?? null;
        }

        if (in_array(self::TEMPERATURE, $include, true)) {
            $data[self::TEMPERATURE] = '';
            if (isset($this->getAdditionalParams()[self::TYPE])) {
                switch ($this->getAdditionalParams()[self::TYPE]) {
                    case self::SENSOR_TYPE_GREATER:
                        $data[self::TEMPERATURE] .= ' > ' . ($this->getAdditionalParams()[self::TEMPERATURE] ?? '--') . ' ' .
                            $translator->trans('set_as_limit', [], Template::TRANSLATE_DOMAIN);
                        break;
                    case self::SENSOR_TYPE_LESS:
                        $data[self::TEMPERATURE] .= ' < ' . ($this->getAdditionalParams()[self::TEMPERATURE] ?? '--') . ' ' .
                            $translator->trans('set_as_limit', [], Template::TRANSLATE_DOMAIN);
                        break;
                    case self::SENSOR_TYPE_OUTSIDE:
                        $data[self::TEMPERATURE] .=
                            ', ' . $translator->trans('outside_range', [], Template::TRANSLATE_DOMAIN) . ' (' .
                            ($this->getAdditionalParams()[self::FROM] ?? '--') . ', '
                            . ($this->getAdditionalParams()[self::TO] ?? '--') . ')';
                        break;
                }
            }
        }

        if (in_array(self::HUMIDITY, $include, true)) {
            $data[self::HUMIDITY] = '';
            if (isset($this->getAdditionalParams()[self::TYPE])) {
                switch ($this->getAdditionalParams()[self::TYPE]) {
                    case self::SENSOR_TYPE_GREATER:
                        $data[self::HUMIDITY] .= ' > ' . ($this->getAdditionalParams()[self::HUMIDITY] ?? '--') . ' ' .
                            $translator->trans('set_as_limit', [], Template::TRANSLATE_DOMAIN);
                        break;
                    case self::SENSOR_TYPE_LESS:
                        $data[self::HUMIDITY] .= ' < ' . ($this->getAdditionalParams()[self::HUMIDITY] ?? '--') . ' ' .
                            $translator->trans('set_as_limit', [], Template::TRANSLATE_DOMAIN);
                        break;
                    case self::SENSOR_TYPE_OUTSIDE:
                        $data[self::HUMIDITY] .=
                            ', ' . $translator->trans('outside_range', [], Template::TRANSLATE_DOMAIN) . ' (' .
                            ($this->getAdditionalParams()[self::FROM] ?? '--') . ', '
                            . ($this->getAdditionalParams()[self::TO] ?? '--') . ')';
                        break;
                }
            }
        }

        if (in_array(self::BATTERY_LEVEL, $include, true)) {
            $data[self::BATTERY_LEVEL] = isset($this->getAdditionalParams()[self::BATTERY_LEVEL])
                ? '< ' . $this->getAdditionalParams()[self::BATTERY_LEVEL] . ' ' .
                $translator->trans('set_as_limit', [], Template::TRANSLATE_DOMAIN) : null;
        }

        if (in_array(self::STATUS_IO, $include, true)) {
            $data[self::STATUS_IO] = $this->getAdditionalParams()[self::STATUS_IO] ?? null;
        }

        if (in_array(self::SENSOR_IO_TYPE_ID, $include, true)) {
            $data[self::SENSOR_IO_TYPE_ID] = $this->getAdditionalParams()[self::SENSOR_IO_TYPE_ID] ?? null;
        }

        if (in_array(self::AREA_TRIGGER_TYPE, $include, true)) {
            $data[self::AREA_TRIGGER_TYPE] = $this->getAreaTriggerType();
        }

        if (in_array(self::EXPRESSION_OPERATOR, $include, true)) {
            $data[self::EXPRESSION_OPERATOR] = $this->getAdditionalParams()[self::EXPRESSION_OPERATOR] ?? null;
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
    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(name: 'importance', type: 'string', length: 255)]
    private $importance;

    /**
     * @var Event
     */
    #[ORM\ManyToOne(targetEntity: 'Event', inversedBy: 'notifications')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private $event;

    /**
     * @var NotificationScopes[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'NotificationScopes', mappedBy: 'notification')]
    private $scopes;

    /**
     * @var NotificationRecipients[]|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'notification', targetEntity: 'NotificationRecipients')]
    private $recipients;

    /**
     * @var NotificationRecipients[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'AcknowledgeRecipients', mappedBy: 'notification')]
    private $acknowledgeRecipients;

    /**
     * @var NotificationTransports[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'NotificationTransports', mappedBy: 'notification')]
    private $transports;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'owner_team_id', referencedColumnName: 'id', nullable: true)]
    private $ownerTeam;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'listener_team_id', referencedColumnName: 'id', nullable: true)]
    private $listenerTeam;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
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
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'event_tracking_time_from', type: 'time', nullable: true)]
    private $eventTrackingTimeFrom;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'event_tracking_time_until', type: 'time', nullable: true)]
    private $eventTrackingTimeUntil;

    /**
     * @var array
     */
    #[ORM\Column(name: 'event_tracking_days', type: 'json', nullable: true)]
    private $eventTrackingDays;

    /**
     * @var string
     */
    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private $comment;

    /**
     * @var array
     */
    #[ORM\Column(name: 'additional_params', type: 'json', nullable: true)]
    private $additionalParams;

    /**
     * @var Acknowledge
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Acknowledge', mappedBy: 'notification', fetch: 'EXTRA_LAZY')]
    private $acknowledge;

    /**
     * @var Message
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Notification\Message', mappedBy: 'notification', fetch: 'EXTRA_LAZY')]
    private $message;

    #[ORM\Column(name: 'send_time_from', type: 'time', nullable: true)]
    private $sendTimeFrom;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'send_time_until', type: 'time', nullable: true)]
    private $sendTimeUntil;

    /**
     * Notification constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->scopes = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->acknowledgeRecipients = new ArrayCollection();
        $this->transports = new ArrayCollection();

        $this->setTitle($fields['title'] ?? null);
        $this->setStatus($fields['status'] ?? self::STATUS_DISABLED);
        $this->setImportance($fields['importance'] ?? null);
        $this->setListenerTeam($fields['listenerTeam'] ?? null);
        $this->setOwnerTeam($fields['ownerTeam'] ?? null);
        $this->setEvent($fields['event'] ?? null);
        $this->setEventTrackingTimeFrom($fields['eventTrackingTimeFrom'] ?? self::DEFAULT_EVENT_TRACKING_TIME_FROM);
        $this->setEventTrackingTimeUntil($fields['eventTrackingTimeUntil'] ?? self::DEFAULT_EVENT_TRACKING_TIME_UNTIL);
        $this->setEventTrackingDays($fields['eventTrackingDays'] ?? self::DEFAULT_EVENT_TRACKING_DAYS);
        $this->setComment($fields['comment'] ?? null);
        $this->setAdditionalParams($fields['additionalParams'] ?? null);
        $this->setSendTimeFrom($fields['sendTimeFrom'] ?? null);
        $this->setSendTimeUntil($fields['sendTimeUntil'] ?? null);
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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return $this
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set importance.
     *
     * @param string|null $importance
     *
     * @return $this
     */
    public function setImportance(?string $importance)
    {
        $this->importance = $importance;

        return $this;
    }

    /**
     * Get importance.
     *
     * @return string
     */
    public function getImportance(): string
    {
        return $this->importance;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     * @return $this
     */
    public function setEvent(?Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return NotificationScopes[]|ArrayCollection
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return array
     */
    public function getScopesArray(): ?array
    {
        return $this->scopes->count()
            ? $this->scopes->first()->toArray(['subtype', 'value'])
            : null;
    }

    /**
     * @param string $category
     * @return array|null
     */
    public function getScopesByCategory(string $category): ?array
    {
        return $this->getNotificationScopes($category)->count()
            ? $this->getNotificationScopes($category)->map(
                static function (NotificationScopes $s) {
                    return $s->toArray(['subtype', 'value']);
                }
            )->first()
            : null;
    }

    /**
     * @param string $category
     * @return ArrayCollection
     */
    public function getNotificationScopes(string $category)
    {
        return $this
            ->getScopes()
            ->filter(
                static function (NotificationScopes $v) use ($category) {
                    return $v->getType()->getCategory() === $category;
                }
            );
    }

    /**
     * @param NotificationScopes[]|ArrayCollection $scopes
     * @return $this
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function addScope(NotificationScopes $scope)
    {
        $this->scopes->add($scope);
        $scope->setNotification($this);
    }

    /**
     * @return NotificationRecipients[]|ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @return array
     */
    public function getRecipientsArray(): array
    {
        return array_values(
            $this->getRecipients()->map(
                static function (NotificationRecipients $s) {
                    return $s->toArray(['type', 'value']);
                }
            )->toArray()
        );
    }

    /**
     * @param NotificationRecipients[]|ArrayCollection $recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function addRecipient(NotificationRecipients $recipient)
    {
        $this->recipients->add($recipient);
        $recipient->setNotification($this);
    }

    /**
     * @return NotificationRecipients[]|ArrayCollection
     */
    public function getAcknowledgeRecipients()
    {
        return $this->acknowledgeRecipients;
    }

    /**
     * @return array
     */
    public function getAcknowledgeRecipientsArray(): array
    {
        return array_values(
            $this->getAcknowledgeRecipients()->map(
                static function (AcknowledgeRecipients $s) {
                    return $s->toArray(['type', 'value']);
                }
            )->toArray()
        );
    }

    /**
     * @param NotificationRecipients[]|ArrayCollection $recipients
     * @return $this
     */
    public function setAcknowledgeRecipients($recipients)
    {
        $this->acknowledgeRecipients = $recipients;

        return $this;
    }

    public function addAcknowledgeRecipient(AcknowledgeRecipients $recipient)
    {
        $this->acknowledgeRecipients->add($recipient);
        $recipient->setNotification($this);
    }

    /**
     * @return NotificationTransports[]|ArrayCollection
     */
    public function getTransports()
    {
        return $this->transports;
    }

    /**
     * @return array
     */
    public function getTransportsArray(): array
    {
        return array_values(
            array_unique(
                $this
                    ->getTransports()
                    ->map(
                        static function (NotificationTransports $t) {
                            return Transport::TRANSPORT_TYPE_TO_SETTING[$t->getTransport()->getAlias()];
                        }
                    )
                    ->toArray()
            )
        );
    }

    /**
     * @param NotificationTransports[]|ArrayCollection $transports
     * @return $this
     */
    public function setTransports($transports)
    {
        $this->transports = $transports;

        return $this;
    }

    public function addTransport(NotificationTransports $transport)
    {
        $this->transports->add($transport);
        $transport->setNotification($this);
    }

    /**
     * @return Team|null
     */
    public function getOwnerTeam(): ?Team
    {
        return $this->ownerTeam;
    }

    /**
     * @return int|null
     */
    public function getOwnerTeamId(): ?int
    {
        return $this->ownerTeam ? $this->ownerTeam->getId() : null;
    }

    /**
     * @return array|null
     */
    public function getOwnerTeamArray(): ?array
    {
        return $this->ownerTeam ? $this->ownerTeam->toArray(['type']) : null;
    }

    /**
     * @param Team|null $ownerTeam
     * @return $this
     */
    public function setOwnerTeam(?Team $ownerTeam)
    {
        $this->ownerTeam = $ownerTeam;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getListenerTeam(): ?Team
    {
        return $this->listenerTeam;
    }

    /**
     * @return int|null
     */
    public function getListenerTeamId(): ?int
    {
        return $this->listenerTeam ? $this->listenerTeam->getId() : null;
    }

    /**
     * @return array|null
     */
    public function getListenerTeamArray(): ?array
    {
        return $this->listenerTeam ? $this->listenerTeam->toArray(['type', 'clientId', 'clientName']) : null;
    }

    /**
     * @param Team|null $listenerTeam
     * @return $this
     */
    public function setListenerTeam(?Team $listenerTeam)
    {
        $this->listenerTeam = $listenerTeam;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
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
     * @return User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @return int|null
     */
    public function getCreatedById(): ?int
    {
        return $this->getCreatedBy()?->getId();
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCreatedByArray(): ?array
    {
        return $this->getCreatedBy()?->toArray(User::CREATED_BY_FIELDS);
    }

    /**
     * @param User $createdBy
     * @return $this
     */
    public function setCreatedBy(?User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }

    /**
     * @return int|null
     */
    public function getUpdatedById(): ?int
    {
        return $this->updatedBy ? $this->getUpdatedBy()->getId() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getUpdatedByArray(): ?array
    {
        return $this->updatedBy ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param User $updatedBy
     * @return $this
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    #[ORM\PrePersist]
    public function createdAt()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getEventTrackingTimeFrom(): string
    {
        return $this->eventTrackingTimeFrom
            ? $this->eventTrackingTimeFrom->format('H:i')
            : self::DEFAULT_EVENT_TRACKING_TIME_FROM;
    }

    /**
     * @param string $eventTrackingTimeFrom
     * @return $this
     */
    public function setEventTrackingTimeFrom(string $eventTrackingTimeFrom)
    {
        $this->eventTrackingTimeFrom = \DateTime::createFromFormat('H:i', $eventTrackingTimeFrom);

        return $this;
    }

    /**
     * @return string
     */
    public function getEventTrackingTimeUntil(): string
    {
        return $this->eventTrackingTimeUntil
            ? $this->eventTrackingTimeUntil->format('H:i')
            : self::DEFAULT_EVENT_TRACKING_TIME_UNTIL;
    }

    /**
     * @param string|null $eventTrackingTimeUntil
     * @return $this
     */
    public function setEventTrackingTimeUntil(string $eventTrackingTimeUntil)
    {
        $this->eventTrackingTimeUntil = \DateTime::createFromFormat('H:i', $eventTrackingTimeUntil);

        return $this;
    }

    /**
     * @return array
     */
    public function getEventTrackingDays()
    {
        return $this->eventTrackingDays;
    }


    /**
     * @param array|null $eventTrackingDays
     * @return $this
     */
    public function setEventTrackingDays(?array $eventTrackingDays)
    {
        $this->eventTrackingDays = $eventTrackingDays;

        return $this;
    }

    /**
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return $this
     */
    public function setComment(?string $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return array|null
     */
    public function getAdditionalParams(): ?array
    {
        return $this->additionalParams;
    }

    /**
     * @param $additionalParams
     * @return $this
     */
    public function setAdditionalParams($additionalParams): self
    {
        $this->additionalParams = $additionalParams;

        return $this;
    }

    public function getAcknowledge()
    {
        return $this->acknowledge;
    }

    /**
     * @param Acknowledge|null $acknowledge
     * @return $this
     */
    public function setAcknowledge(?Acknowledge $acknowledge): self
    {
        $this->acknowledge = $acknowledge;

        return $this;
    }

    public function hasAcknowledge()
    {
        return $this->acknowledgeRecipients->count();
    }

    public function getLanguageValue(): string
    {
        return $this->getOwnerTeam()->getSettingsByName(Setting::LANGUAGE_SETTING)
            ? $this->getOwnerTeam()->getSettingsByName(Setting::LANGUAGE_SETTING)->getValue()
            : Setting::LANGUAGE_SETTING_DEFAULT_VALUE;
    }

    public function getAreaTriggerType(): ?string
    {
        return $this->getAdditionalParams()[self::AREA_TRIGGER_TYPE] ?? null;
    }

    public function isAreaTriggerTypeEverywhere(): bool
    {
        return $this->getAreaTriggerType() === self::AREA_TRIGGER_TYPE_EVERYWHERE;
    }

    public function isAreaTriggerTypeInside(): bool
    {
        return $this->getAreaTriggerType() === self::AREA_TRIGGER_TYPE_INSIDE;
    }

    public function isAreaTriggerTypeOutside(): bool
    {
        return $this->getAreaTriggerType() === self::AREA_TRIGGER_TYPE_OUTSIDE;
    }

    public function isTimeDuration(): bool
    {
        return isset($this->getAdditionalParams()[self::TIME_DURATION]);
    }

    public function isDistance(): bool
    {
        return isset($this->getAdditionalParams()[self::DISTANCE]);
    }

    public function getOverSpeedParam()
    {
        return $this->getAdditionalParams()[self::OVER_SPEED] ?? null;
    }

    public function getVoltageParam()
    {
        return $this->getAdditionalParams()[self::DEVICE_VOLTAGE] ?? null;
    }

    public function getDistanceParam()
    {
        return $this->getAdditionalParams()[self::DISTANCE] ?? null;
    }

    public function getTimeDurationParam()
    {
        return $this->getAdditionalParams()[self::TIME_DURATION] ?? null;
    }

    public function getThresholdParam()
    {
        return $this->getAdditionalParams()[self::THRESHOLD_SPEED_LIMIT] ?? null;
    }

    /**
     * @return bool
     */
    public function isExpressionOperator(): bool
    {
        return isset($this->getAdditionalParams()[self::EXPRESSION_OPERATOR]);
    }

    /**
     * @return mixed|null
     */
    public function getExpressionOperator()
    {
        return $this->isExpressionOperator() ? $this->getAdditionalParams()[self::EXPRESSION_OPERATOR] : null;
    }

    public function getTimeFromTo(): array
    {
        $timeFrom = clone $this->eventTrackingTimeFrom;
        $timeTo = clone $this->eventTrackingTimeUntil;
        $result = [];
        if ($timeFrom < $timeTo) {
            for ($time = $timeFrom; $time <= $timeTo; $time->add(new \DateInterval('PT1M'))) {
                $result[] = $time->format('H:i');
            }
        } elseif ($timeFrom > $timeTo) {
            $midnight = Carbon::instance($timeFrom)->endOfDay();
            for ($time = $timeFrom; $time <= $midnight; $time->add(new \DateInterval('PT1M'))) {
                $result[] = $time->format('H:i');
            }
            $midnight = Carbon::instance($timeTo)->startOfDay();
            for ($time = $midnight; $time <= $timeTo; $time->add(new \DateInterval('PT1M'))) {
                $result[] = $time->format('H:i');
            }
        } elseif ($timeFrom == $timeTo) {
            $timeTo = Carbon::instance($timeTo)->endOfDay();
            for ($time = $timeFrom; $time <= $timeTo; $time->add(new \DateInterval('PT1M'))) {
                $result[] = $time->format('H:i');
            }
        }

        return $result;
    }

    public function setSendTimeFrom(?string $sendTimeFrom): self
    {
        if (!$sendTimeFrom) {
            $this->sendTimeFrom = null;
        } else {
            $this->sendTimeFrom = \DateTime::createFromFormat('H:i', $sendTimeFrom);
        }

        return $this;
    }

    public function getSendTimeFrom(): ?string
    {
        return $this->sendTimeFrom?->format('H:i');
    }

    public function setSendTimeUntil(?string $sendTimeUntil): self
    {
        if (!$sendTimeUntil) {
            $this->sendTimeUntil = null;
        } else {
            $this->sendTimeUntil = \DateTime::createFromFormat('H:i', $sendTimeUntil);
        }

        return $this;
    }

    public function getSendTimeUntil(): ?string
    {
        return $this->sendTimeUntil?->format('H:i');
    }

    public function getClientTimezone(): ?string
    {
        return $this->getOwnerTeam()?->getClient()?->getTimeZoneName() ?? TimeZone::DEFAULT_TIMEZONE['name'];
    }
}
