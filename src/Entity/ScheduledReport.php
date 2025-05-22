<?php

namespace App\Entity;

use App\Service\ScheduledReport\Report;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Cron\CronExpression;
use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduledReport
 */
#[ORM\Table(name: 'scheduled_report')]
#[ORM\Entity(repositoryClass: 'App\Repository\ScheduledReportRepository')]
#[ORM\EntityListeners(['App\EventListener\ScheduledReport\ScheduledReportEntityListener'])]
class ScheduledReport extends BaseEntity
{
    use AttributesTrait;

    public const INTERVAL_DAILY = 'daily';
    public const INTERVAL_WEEKLY = 'weekly';
    public const INTERVAL_MONTHLY = 'monthly';

    public const FORMAT_CSV = 'csv';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_XLSX = 'xlsx';

    public const EDITABLE_FIELDS = [
        'name',
        'type',
        'status',
        'interval',
        'format',
        'recipient',
        'params',
        'timezone'
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'type',
        'status',
        'interval',
        'format',
        'recipients',
        'params',
        'createdAt',
        'updatedAt',
        'timezone'
    ];

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED,
        self::STATUS_DISABLED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DISABLED
    ];

    public function __construct(array $fields = [])
    {
        $this->team = $fields['team'] ?? null;
        $this->type = $fields['type'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->name = $fields['name'] ?? null;
        $this->interval = $fields['interval'] ?? null;
        $this->format = $fields['format'] ?? null;
        $this->recipient = $fields['recipient'] ?? null;
        $this->params = $fields['params'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->timezone = $fields['timezone'] ?? null;
        $this->sentAt = new \DateTime();
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('interval', $include, true)) {
            $data['interval'] = $this->getInterval();
        }
        if (in_array('format', $include, true)) {
            $data['format'] = $this->getFormat();
        }
        if (in_array('recipients', $include, true)) {
            $data['recipients'] = $this->getRecipientData();
        }
        if (in_array('params', $include, true)) {
            $data['params'] = $this->getParams();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('timezone', $include, true)) {
            $data['timezone'] = $this->getTimezoneEntity()?->toArray();
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
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'interval', type: 'json')]
    private $interval;

    /**
     * @var string
     */
    #[ORM\Column(name: 'format', type: 'string', length: 50)]
    private $format;

    /**
     * @var ScheduledReportRecipients
     */
    #[ORM\OneToOne(targetEntity: 'ScheduledReportRecipients', inversedBy: 'scheduledReport')]
    #[ORM\JoinColumn(name: 'recipient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $recipient;

    /**
     * @var array
     */
    #[ORM\Column(name: 'params', type: 'json', options: ['jsonb' => true], nullable: true)]
    private $params;

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
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'sent_at', type: 'datetime', nullable: true)]
    private $sentAt;

    private $recipientData = [];

    /**
     * @var TimeZone
     */
    #[ORM\ManyToOne(targetEntity: 'TimeZone')]
    #[ORM\JoinColumn(name: 'timezone_id', referencedColumnName: 'id', nullable: true)]
    private $timezone;


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
     * Set type.
     *
     * @param string $type
     *
     * @return ScheduledReport
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
     * Set name.
     *
     * @param string $name
     *
     * @return ScheduledReport
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
     * Set interval.
     *
     * @param string $interval
     *
     * @return ScheduledReport
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval.
     *
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set format.
     *
     * @param string $format
     *
     * @return ScheduledReport
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return ScheduledReport
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
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return ScheduledReport
     */
    public function setCreatedAt($createdAt)
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
     * Set createdBy.
     *
     * @param User $createdBy
     *
     * @return ScheduledReport
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return ScheduledReport
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

    /**
     * Set updatedBy.
     *
     * @param \DateTime|null $updatedBy
     *
     * @return ScheduledReport
     */
    public function setUpdatedBy($updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy.
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function setRecipient(ScheduledReportRecipients $recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return ScheduledReportRecipients
     */
    public function getRecipient(): ScheduledReportRecipients
    {
        return $this->recipient;
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

    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    public function getParams()
    {
        return $this->params ?? [];
    }

    public function getIntervalType()
    {
        return isset($this->getInterval()['type']) ? $this->getInterval()['type'] : null;
    }

    public function getIntervalTimeUTC()
    {
        return isset($this->getInterval()['time'])
            ? (new Carbon($this->getInterval()['time'], $this->getTimezone()))->setTimezone('UTC')
            : null;
    }

    public function getIntervalTime()
    {
        return isset($this->getInterval()['time'])
            ? (new Carbon($this->getInterval()['time'], $this->getTimezone()))
            : null;
    }

    public function getPeriod()
    {
        return isset($this->getInterval()['period']) ? Report::periodMapper($this->getInterval()['period']) : null;
    }

    public function getPeriodDays()
    {
        return $this->getIntervalObject()->totalDays;
    }

    public function getDay()
    {
        if (isset($this->getInterval()['day'])) {
            $time = $this->getIntervalTime();
            if ($this->isIntervalMonthly()) {
                $time->day($this->getInterval()['day']);

                return $time->day;
            } elseif ($this->isIntervalWeekly()) {
                $time->next((int)$this->getInterval()['day']);

                return $time->dayOfWeek;
            }

        } else {
            return null;
        }
    }

    public function getIntervalObject()
    {
        return CarbonInterval::fromString($this->getPeriod());
    }

    public function isIntervalDaily(): bool
    {
        return $this->getIntervalType() === self::INTERVAL_DAILY;
    }

    public function isIntervalWeekly(): bool
    {
        return $this->getIntervalType() === self::INTERVAL_WEEKLY;
    }

    public function isIntervalMonthly(): bool
    {
        return $this->getIntervalType() === self::INTERVAL_MONTHLY;
    }

    public function getSentAt()
    {
        return $this->sentAt;
    }

    public function setSentAt($datetime)
    {
        $this->sentAt = $datetime;

        return $this;
    }

    public function getCronExpression()
    {
        $hour = $this->getIntervalTime()->hour;
        $minute = $this->getIntervalTime()->minute;
        $day = $this->getDay();

        if ($this->isIntervalDaily()) {
            return new CronExpression($minute . ' ' . $hour . ' * * *');
        } elseif ($this->isIntervalWeekly()) {
            return new CronExpression($minute . ' ' . $hour . ' * * ' . $day);
        } elseif ($this->isIntervalMonthly()) {
            return new CronExpression($minute . ' ' . $hour . ' ' . $day . ' * *');
        }

        return null;
    }

    public function getStartAndEndDate(?\DateTime $dateTime = null)
    {
        $dateTime = $dateTime ? Carbon::instance($dateTime) : (new Carbon());
        $dateTime->setTimeZone($this->getTimezone());
        $data = [];
        $data['startDate'] = null;
        $data['endDate'] = null;
        if ($this->isIntervalDaily()) {
            $interval = $this->getIntervalObject();
            $data['startDate'] = (clone $dateTime)->sub($interval)->startOfDay();
            $data['endDate'] = (clone $dateTime)->subDays(1)->endOfDay();
        } elseif ($this->isIntervalWeekly()) {
            $data['startDate'] = (clone $dateTime)->subWeek()->startOfWeek();
            $data['endDate'] = (clone $dateTime)->subWeek()->endOfWeek();
        } elseif ($this->isIntervalMonthly()) {
            $interval = $this->getIntervalObject();
            $data['startDate'] = (clone $dateTime)->sub($interval)->startOfMonth();
            $data['endDate'] = (clone $dateTime)->subMonthNoOverflow()->endOfMonth();
        }
        $data['startDate'] = $data['startDate'] ? $data['startDate']->setTimeZone('UTC') : null;
        $data['endDate'] = $data['endDate'] ? $data['endDate']->setTimeZone('UTC') : null;

        return $data;
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone?->getName() ?? $this->getCreatedBy()->getTimezone());
    }

    public function getTimezoneName(): string
    {
        return $this->timezone?->getName() ?? $this->getTeam()->getTimezoneName();
    }

    public function getTimezoneEntity(): ?TimeZone
    {
        return $this->timezone;
    }

    public function getDateFormat(): string
    {
        return $this->getCreatedBy()->getDateFormatSettingConverted();
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()->getId();
    }

    public function setRecipientData(array $data): self
    {
        $this->recipientData = $data;

        return $this;
    }

    public function getRecipientData(): array
    {
        return $this->recipientData;
    }

    public function getEventId(): ?int
    {
        return $this->getParams()['eventId'] ?? null;
    }
}
