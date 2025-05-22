<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalFormSchedule
 */
#[ORM\Table(name: 'digital_form_schedule')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormScheduleRepository')]
class DigitalFormSchedule extends BaseEntity
{
    /** @var int */
    public const WEIGHT_DEFAULT = 0;

    /** @var string */
    public const DAY_MONDAY = "monday";
    public const DAY_TUESDAY = "tuesday";
    public const DAY_WEDNESDAY = "wednesday";
    public const DAY_THURSDAY = "thursday";
    public const DAY_FRIDAY = "friday";
    public const DAY_SATURDAY = "saturday";
    public const DAY_SUNDAY = "sunday";

    /** @var array */
    public const VALID_DAYS = [
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
        self::DAY_SATURDAY,
        self::DAY_SUNDAY,
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_default', type: 'boolean', nullable: false, options: ['default' => false])]
    private $isDefault;

    /**
     * @var int
     */
    #[ORM\Column(name: 'weight', type: 'smallint')]
    private $weight = self::WEIGHT_DEFAULT;

    /**
     * @var DigitalForm
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalForm')]
    #[ORM\JoinColumn(name: 'digital_form_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalForm;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $createdBy;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'DigitalFormScheduleRecipient', mappedBy: 'digitalFormSchedule', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $digitalFormScheduleRecipients;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'time_from', type: 'time', nullable: true)]
    private $timeFrom;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'time_to', type: 'time', nullable: true)]
    private $timeTo;

    /**
     * @var array
     */
    #[ORM\Column(name: 'days', type: 'json', options: ['jsonb' => true], nullable: true)]
    private $days;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'day_of_month', type: 'smallint', nullable: true)]
    private $dayOfMonth = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->digitalFormScheduleRecipients = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setWeight($weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setDigitalForm(DigitalForm $digitalForm): self
    {
        $this->digitalForm = $digitalForm;

        return $this;
    }

    public function getDigitalForm(): DigitalForm
    {
        return $this->digitalForm;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getDigitalFormScheduleRecipients(): Collection
    {
        return $this->digitalFormScheduleRecipients;
    }

    public function addDigitalFormScheduleRecipient(DigitalFormScheduleRecipient $digitalFormScheduleRecipient): self
    {
        $this->digitalFormScheduleRecipients->add($digitalFormScheduleRecipient);

        return $this;
    }

    public function setTimeFrom($timeFrom): self
    {
        $this->timeFrom = $timeFrom;

        return $this;
    }

    public function getTimeFrom()
    {
        return $this->timeFrom;
    }

    public function setTimeTo($timeTo): self
    {
        $this->timeTo = $timeTo;

        return $this;
    }

    public function getTimeTo()
    {
        return $this->timeTo;
    }

    public function setDays($days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getDays()
    {
        return $this->days;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getDayOfMonth(): ?int
    {
        return $this->dayOfMonth;
    }

    /**
     * @param int|null $dayOfMonth
     *
     * @return $this
     */
    public function setDayOfMonth(?int $dayOfMonth): self
    {
        $this->dayOfMonth = $dayOfMonth;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId(),
            'weight' => $this->getWeight(),
            'timeFrom' => $this->getTimeFrom() ? $this->getTimeFrom()->format('H:i') : $this->getTimeFrom(),
            'timeTo' => $this->getTimeTo() ? $this->getTimeTo()->format('H:i') : $this->getTimeTo(),
            'days' => $this->getDays(),
            'dayOfMonth' => $this->getDayOfMonth(),
            'creator' => $this->getCreatedBy()->getFullName(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
        ];

        if (in_array('form', $include, true)) {
            $data['form'] = $this->getDigitalForm()->toArray($include);
        }
        if (in_array('creator', $include, true)) {
            $data['creator'] = $this->getCreatedBy()->toArray($include);
        }
        if (in_array('recipients', $include, true)) {
            // only one item can be
            $item = $this->getDigitalFormScheduleRecipients()->first();
            $data['recipients'] = $item ? $item->toArray($include) : null;
        }

        return $data;
    }

    public function getVehicleCount(): int
    {
        // for more info see \App\EventListener\ElasticSearch\ClientListener::getVehicleCount()
        return 0;
    }
}
