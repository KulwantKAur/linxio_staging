<?php

namespace App\Entity;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Notification;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Acknowledge
 */
#[ORM\Table(name: 'acknowledge')]
#[ORM\Entity(repositoryClass: 'App\Repository\AcknowledgeRepository')]
class Acknowledge extends BaseEntity
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_REVIEW = 'in-review';
    public const STATUS_ACTIONED = 'actioned';

    public const ALLOWED_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_REVIEW,
        self::STATUS_ACTIONED
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'status',
        'comment',
        'recipients'
    ];

    /**
     * Acknowledge constructor.
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->notification = $fields['notification'] ?? null;
        $this->eventLog = $fields['eventLog'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_OPEN;
        $this->comment = $fields['comment'] ?? null;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('notification', $include, true)) {
            $data['notification'] = $this->getNotification()->toArray();
        }
        if (in_array('eventLog', $include, true)) {
            $data['eventLog'] = $this->getEventLog()->toArray();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('comment', $include, true)) {
            $data['comment'] = $this->getComment();
        }
        if (in_array('recipients', $include, true)) {
            $data['recipients'] = $this->getNotification()->getAcknowledgeRecipientsArray();
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
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Notification', inversedBy: 'acknowledge')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false)]
    private $notification;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\EventLog\EventLog', inversedBy: 'acknowledge')]
    #[ORM\JoinColumn(name: 'event_log_id', referencedColumnName: 'id', nullable: false)]
    private $eventLog;

    /**
     * @var string
     * @Assert\Choice(
     *     choices = { Acknowledge::STATUS_OPEN, Acknowledge::STATUS_IN_REVIEW, Acknowledge::STATUS_ACTIONED },
     *     message = "Choose a valid status."
     * )
     */
    #[ORM\Column(name: 'status', type: 'string', length: 50, nullable: false)]
    private $status;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private $comment;


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
     * Set notification.
     *
     * @param Notification $notification
     *
     * @return Acknowledge
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification.
     *
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * Set eventLog.
     *
     * @param EventLog $eventLog
     *
     * @return Acknowledge
     */
    public function setEventLog(EventLog $eventLog)
    {
        $this->eventLog = $eventLog;

        return $this;
    }

    /**
     * Get eventLog.
     *
     * @return EventLog
     */
    public function getEventLog(): EventLog
    {
        return $this->eventLog;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Acknowledge
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
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return Acknowledge
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }
}
