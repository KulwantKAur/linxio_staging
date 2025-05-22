<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use App\Entity\EventLog\EventLog;
use App\Entity\Team;
use App\Entity\User;
use App\Util\AttributesTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 */
#[ORM\Table(name: 'notification_message')]
#[ORM\Index(name: 'notification_message_duplicate_index', columns: ['notification_id', 'occurrence_time', 'transport_type', 'recipient'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\MessageRepository')]
class Message extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_SUBJECT = '[Linxio] Notification Message';

    public const TYPE_PENDING = 'pending';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_DELIVERED = 'delivered';
    public const TYPE_DUPLICATED = 'duplicated';

    public const DEFAULT_SOUND = 'default';
    public const PUSH_TYPE_FOR_CHAT = 'linxioChat';
    public const PUSH_TYPE_FOR_UNASSIGNED_DRIVER = 'linxioUnassignedDriver';
    public const PUSH_TYPE_USER_LOGOUT = 'linxioUserLogout';

    public const ALLOWED_TYPES = [
        self::TYPE_PENDING,
        self::TYPE_DELIVERY,
        self::TYPE_DELIVERED,
    ];

    public const MESSAGE_EVENT_SOURCE_TYPE = 'eventSourceType';
    public const MESSAGE_ENTITY_TEAM = 'entityTeam';
    public const MESSAGE_EVENT_NAME = 'eventName';
    public const MESSAGE_EVENT_EVENT_LOG_ID = 'eventLogId';
    public const MESSAGE_EVENT_EVENT_ID = 'eventId';
    public const MSG_EVENT_SHORT_DETAILS = 'eventShortDetails';

    public const DISPLAYED_VALUES = [
        'id',
//        'transportType',
        'recipient',
        'subject',
        'message',
//        'sendingTime',
        'occurrenceTime',
//        'status',
        'event',
        'isRead',
        'acknowledge'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'transport_type', type: 'string', length: 255)]
    private $transportType;

    /**
     * @var string
     */
    #[ORM\Column(name: 'recipient', type: 'string', length: 255)]
    private $recipient;

    /**
     * @var array
     */
    #[ORM\Column(name: 'body', type: 'json')]
    private $body;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'sending_time', type: 'datetime')]
    private $sendingTime;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'processing_time', type: 'datetime')]
    private $processingTime;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'occurrence_time', type: 'datetime')]
    private $occurrenceTime;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_read', type: 'boolean', nullable: false, options: ['default' => false])]
    private $isRead = false;

    /**
     * @var EventLog
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\EventLog\EventLog', inversedBy: 'message')]
    #[ORM\JoinColumn(name: 'event_log_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $eventLog;

    /**
     * @var Notification
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Notification', inversedBy: 'message')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $notification;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'update_at', type: 'datetime', nullable: true)]
    private $updateAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', inversedBy: 'message')]
    #[ORM\JoinColumn(name: 'update_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updateBy;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sender', type: 'string', length: 255, nullable: true)]
    private $sender;

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('transportType', $include, true)) {
            $data['transportType'] = $this->getTransportType();
        }

        if (in_array('recipient', $include, true)) {
            $data['recipient'] = $this->getRecipient();
        }

        if (in_array('subject', $include, true)) {
            $data['subject'] = $this->getBodySubject();
        }

        if (in_array('message', $include, true)) {
            $data['message'] = $this->getBodyMessage();
        }

        if (in_array('sendingTime', $include, true)) {
            $data['sendingTime'] = $this->formatDate($this->getSendingTime());
        }

        if (in_array('occurrenceTime', $include, true)) {
            $data['occurrenceTime'] = $this->formatDate($this->getOccurrenceTime());
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('event', $include, true)) {
            $eventDetails = $this->getEventLog()?->toArray(['id', 'entityId', 'details', 'event', 'vehicleId']);
            $data['event'] = $this->prepareEventFields($eventDetails);
        }

        if (in_array('isRead', $include, true)) {
            $data['isRead'] = $this->isRead();
        }

        if (in_array('acknowledge', $include, true)) {
            $data['acknowledge'] = $this->getAcknowledge()
                ? $this->getAcknowledge()->toArray(['status', 'comment', 'recipients'])
                : null;
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
     * Set transportType.
     *
     * @param string $transportType
     *
     * @return Message
     */
    public function setTransportType($transportType)
    {
        $this->transportType = $transportType;

        return $this;
    }

    /**
     * Get transportType.
     *
     * @return string
     */
    public function getTransportType()
    {
        return $this->transportType;
    }

    /**
     * Set recipient.
     *
     * @param string $recipient
     *
     * @return Message
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient.
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getBodySubject(): string
    {
        return $this->body['subject'] ?? self::DEFAULT_SUBJECT;
    }

    /**
     * @return string
     */
    public function getBodyMessage(): string
    {
        return $this->body['body'] ?? '';
    }

    public function getTimezone(): string
    {
        return $this->body['timezone'] ?? '';
    }

    /**
     * @param array $body
     * @return $this
     */
    public function setBody(array $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Message
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set sendingTime.
     *
     * @param \DateTime $sendingTime
     *
     * @return Message
     */
    public function setSendingTime(\DateTime $sendingTime)
    {
        $this->sendingTime = $sendingTime;

        return $this;
    }

    /**
     * Get sendingTime.
     *
     * @return \DateTime
     */
    public function getSendingTime()
    {
        return $this->sendingTime;
    }

    /**
     * @return \DateTime
     */
    public function getProcessingTime(): \DateTime
    {
        return $this->processingTime;
    }

    /**
     * @param \DateTime $processingTime
     * @return $this
     */
    public function setProcessingTime(\DateTime $processingTime)
    {
        $this->processingTime = $processingTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOccurrenceTime(): \DateTime
    {
        return $this->occurrenceTime;
    }

    /**
     * @param \DateTime $occurrenceTime
     * @return $this
     */
    public function setOccurrenceTime(\DateTime $occurrenceTime)
    {
        $this->occurrenceTime = $occurrenceTime;

        return $this;
    }

    /**
     * @param EventLog|object $eventLog
     * @return $this
     */
    public function setEventLog(EventLog $eventLog)
    {
        $this->eventLog = $eventLog;

        return $this;
    }

    /**
     * @return EventLog
     */
    public function getEventLog(): EventLog
    {
        return $this->eventLog;
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->isRead;
    }

    /**
     * @param bool $isRead
     */
    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }


    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdateAt(): ?\DateTime
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime|null $updateAt
     */
    public function setUpdateAt(?\DateTime $updateAt): void
    {
        $this->updateAt = $updateAt;
    }

    /**
     * @return User
     */
    public function getUpdateBy(): User
    {
        return $this->updateBy;
    }

    /**
     * @param User $updateBy
     */
    public function setUpdateBy(User $updateBy): void
    {
        $this->updateBy = $updateBy;
    }

    /**
     * @param array $event
     * @return array
     */
    public function prepareEventFields(array $event)
    {
        $eventDetails = [];
        $entity = ClassUtils::getRealClass($event['event']['entity']);

        $eventDetails[self::MESSAGE_ENTITY_TEAM] = $event['details']['team'] ?? null;
        $eventDetails[self::MESSAGE_EVENT_NAME] = $event['event']['name'] ?? null;
        $eventDetails[self::MESSAGE_EVENT_EVENT_LOG_ID] = $event['id'] ?? null;
        $eventDetails[self::MESSAGE_EVENT_EVENT_ID] = $event['event']['id'] ?? null;

        switch ($entity) {
            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                $eventDetails[self::MESSAGE_ENTITY_TEAM] = $event['details']['device']['team'] ?? null;
                break;
            case Event::ENTITY_TYPE_AREA_HISTORY:
                // TODO: in the future add main id for each event
                if ($event['vehicleId'] ?? null) {
                    $eventDetails[self::MSG_EVENT_SHORT_DETAILS]['vehicleId'] = $event['vehicleId'];
                }
                if ($event['details']['area']['id'] ?? null) {
                    $eventDetails[self::MSG_EVENT_SHORT_DETAILS]['areaId'] = $event['details']['area']['id'];
                }
                break;
            default:
                break;
        }

        $eventDetails[self::MESSAGE_ENTITY_TEAM] =
            array_intersect_key(
                $eventDetails[self::MESSAGE_ENTITY_TEAM] ?? [],
                array_flip(['id', 'type'])
            );

        return $eventDetails;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender($sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getAcknowledge()
    {
        return $this->getEventLog()->getAcknowledge()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('notification', $this->getNotification()))
        )->first();
    }

    public function getTeam(): Team
    {
        return $this->getNotification()->getOwnerTeam();
    }
}
