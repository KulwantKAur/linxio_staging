<?php

namespace App\Entity\Notification\Alert;

use App\Entity\BaseEntity;
use App\Entity\Notification\Event;
use App\Entity\Plan;
use Doctrine\ORM\Mapping as ORM;

/**
 * AlertSetting
 */
#[ORM\Table(name: 'notification_alert_setting')]
#[ORM\UniqueConstraint(columns: ['team', 'event_id', 'alert_type_id', 'alert_subtype_id', 'plan_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\Alert\AlertSettingRepository')]
class AlertSetting extends BaseEntity
{
    public const DISPLAYED_VALUES = [
        'team',
        'alertType',
        'alertSubType',
        'events',
        'sort',
    ];

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam();
        }

        if (in_array('alertType', $include, true)) {
            $data['alertType'] = $this->getAlertTypeArray();
        }

        if (in_array('alertSubType', $include, true)) {
            $data['alertSubType'] = $this->getAlertSubtypeArray();
        }

        if (in_array('events', $include, true)) {
            $data['events'] = $this->getEventArray();
        }

        if (in_array('sort', $include, true)) {
            $data['sort'] = $this->getSort();
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
    #[ORM\Column(name: 'team', type: 'string', length: 50)]
    private $team;

    /**
     * @var Event
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Event')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private $event;

    /**
     * @var AlertType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Alert\AlertType')]
    #[ORM\JoinColumn(name: 'alert_type_id', referencedColumnName: 'id', nullable: false)]
    private $alertType;

    /**
     * @var AlertSubType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Notification\Alert\AlertSubType', inversedBy: 'alertSettings')]
    #[ORM\JoinColumn(name: 'alert_subtype_id', referencedColumnName: 'id', nullable: false)]
    private $alertSubtype;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'integer', options: ['default' => 0])]
    private $sort;

    /**
     * @var Plan
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Plan')]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id')]
    private $plan;


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
     * Set team.
     *
     * @param string $team
     *
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set event.
     *
     * @param Event $event
     *
     * @return AlertSetting
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
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
        return $this->getEvent()->toArray(['id', 'name', 'alias', 'eventSource', 'notifications']);
    }

    /**
     * Set alertType.
     *
     * @param AlertType $alertType
     *
     * @return AlertSetting
     */
    public function setAlertType(AlertType $alertType)
    {
        $this->alertType = $alertType;

        return $this;
    }

    /**
     * Get alertType.
     *
     * @return AlertType
     */
    public function getAlertType(): AlertType
    {
        return $this->alertType;
    }

    /**
     * @return array
     */
    public function getAlertTypeArray(): array
    {
        return $this->getAlertType()->toArray(['id', 'name']);
    }

    /**
     * Set alertSubtype.
     *
     * @param AlertSubType $alertSubtype
     *
     * @return AlertSetting
     */
    public function setAlertSubtype(AlertSubType $alertSubtype)
    {
        $this->alertSubtype = $alertSubtype;

        return $this;
    }

    /**
     * Get alertSubtype.
     *
     * @return AlertSubType
     */
    public function getAlertSubtype(): AlertSubType
    {
        return $this->alertSubtype;
    }

    /**
     * @return array
     */
    public function getAlertSubtypeArray(): array
    {
        return $this->getAlertSubtype()->toArray(['id', 'name']);
    }

    /**
     * Set sort.
     *
     * @param int $sort
     *
     * @return AlertSetting
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set plan
     *
     * @param Plan $plan
     *
     * @return self
     */
    public function setPlan(?Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return Plan
     */
    public function getPlan(): ?Plan
    {
        return $this->plan;
    }
}
