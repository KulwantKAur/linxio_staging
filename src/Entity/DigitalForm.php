<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalForm
 */
#[ORM\Table(name: 'digital_forms')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormRepository')]
class DigitalForm extends BaseEntity
{
    /** @var string */
    public const TYPE_AREA = 'area';
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_REPAIR_COST = 'repairCost';

    /** @var array */
    public const VALID_TYPES = [
        self::TYPE_AREA,
        self::TYPE_INSPECTION,
        self::TYPE_MAINTENANCE,
        self::TYPE_REPAIR_COST,
    ];

    /** @var string */
    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;
    public const STATUS_UNAVAILABLE = 'unavailable';

    /** @var array */
    public const STATUS_VALID_TYPES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED,
        self::STATUS_UNAVAILABLE,
    ];

    /** @var array */
    public const STATUS_ACTIVE_TYPES = [
        self::STATUS_ACTIVE,
        self::STATUS_UNAVAILABLE,
    ];

    public const INSPECTION_PERIOD_SHOW_ALWAYS = 'inspectionFormPeriodShowAlways';
    public const INSPECTION_PERIOD_EVERY_TIME = 'inspectionFormPeriodEveryTime';
    public const INSPECTION_PERIOD_ONCE_PER_DAY = 'inspectionFormPeriodOncePerDay';
    public const INSPECTION_PERIOD_ONCE_PER_WEEK = 'inspectionFormPeriodOncePerWeek';
    public const INSPECTION_PERIOD_ONCE_PER_MONTH = 'inspectionFormPeriodOncePerMonth';

    public const INSPECTION_PERIOD_VALID_TYPES = [
        null,
        self::INSPECTION_PERIOD_SHOW_ALWAYS,
        self::INSPECTION_PERIOD_EVERY_TIME,
        self::INSPECTION_PERIOD_ONCE_PER_DAY,
        self::INSPECTION_PERIOD_ONCE_PER_WEEK,
        self::INSPECTION_PERIOD_ONCE_PER_MONTH,
    ];

    public const INSPECTION_PERIOD_TEXT_VALUES = [
        self::INSPECTION_PERIOD_SHOW_ALWAYS => 'Show Always',
        self::INSPECTION_PERIOD_EVERY_TIME => 'Every time new driver assigned',
        self::INSPECTION_PERIOD_ONCE_PER_DAY => 'Once per day',
        self::INSPECTION_PERIOD_ONCE_PER_WEEK => 'Weekly',
        self::INSPECTION_PERIOD_ONCE_PER_MONTH => 'Monthly'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 64)]
    private $type;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'active', type: 'boolean', nullable: false)]
    private $active;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 32, nullable: false, options: ['default' => 'active'])]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(name: 'title', type: 'string', length: 1024)]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private $description;

    /**
     * @var int
     */
    #[ORM\Column(name: 'old_id', type: 'integer', nullable: false, options: ['default' => 0])]
    private $oldId = 0;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $team;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private $createdAt;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'DigitalFormStep', mappedBy: 'digitalForm', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $digitalFormSteps;

    /**
     * @var string
     */
    #[ORM\Column(name: 'inspection_period', type: 'string', length: 60, nullable: true)]
    private $inspectionPeriod;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DigitalFormAnswer', mappedBy: 'digitalForm', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $digitalFormAnswers;

    /**
     * @var array
     */
    #[ORM\Column(name: 'emails', type: 'json', nullable: true)]
    private ?array $emails = [];

    /**
     * DigitalForm constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->digitalFormSteps = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

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
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $oldId
     *
     * @return $this
     */
    public function setOldId(int $oldId): self
    {
        $this->oldId = $oldId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOldId(): int
    {
        return $this->oldId;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     *
     * @return $this
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @param User $createdBy
     *
     * @return $this
     */
    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

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
     * @return Collection
     */
    public function getDigitalFormSteps(): Collection
    {
        return $this->digitalFormSteps;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId(),
            'active' => $this->getActive(),
            'status' => $this->getStatus(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'creator' => $this->getCreatedBy()->getFullName(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'inspectionPeriod' => $this->getInspectionPeriod(),
            'emails' => $this->getEmails()
        ];

        if (in_array('creator', $include, true)) {
            $data['creator'] = $this->getCreatedBy()->toArray(User::SIMPLE_VALUES);
        }
        if (in_array('steps', $include, true)) {
            foreach ($this->getDigitalFormSteps() as $item) {
                $data['steps'][] = $item->toArray();
            }
        }

        return $data;
    }

    /**
     * @return string|null
     */
    public function getInspectionPeriod(): ?string
    {
        return $this->inspectionPeriod;
    }

    /**
     * @param string|null $inspectionPeriod
     *
     * @return $this
     */
    public function setInspectionPeriod(?string $inspectionPeriod): self
    {
        $this->inspectionPeriod = $inspectionPeriod;

        return $this;
    }

    public function isTypeInspection()
    {
        return $this->getType() === self::TYPE_INSPECTION;
    }

    public function getEmails(): ?array
    {
        return $this->emails ?? [];
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }
}
