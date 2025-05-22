<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReminderCategory
 */
#[ORM\Table(name: 'reminder_category')]
#[ORM\Entity(repositoryClass: 'App\Repository\ReminderCategoryRepository')]
#[ORM\EntityListeners(['App\EventListener\ReminderCategory\ReminderCategoryEntityListener'])]
class ReminderCategory extends BaseEntity
{
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'status',
        'createdAt',
        'updatedAt',
        'reminders',
        'order'
    ];

    public const LIST_DISPLAY_VALUES = [
        'name',
        'status',
        'createdAt',
        'updatedAt',
        'order'
    ];

    /**
     * ReminderCategory constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->createdAt = $fields['createdAt'] ?? new \DateTime();
        $this->reminders = new ArrayCollection();
        $this->order = $fields['order'] ?? 0;
        $this->fixedMileage = $fields['fixedMileage'] ?? false;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
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

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }

        if (in_array('order', $include, true)) {
            $data['order'] = $this->getOrder();
        }

        return $data;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toExport(array $include = []): array
    {
        $data = [];

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
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
    #[ORM\Column(name: 'status', type: 'string', length: 50)]
    private $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updatedAt', type: 'datetime', nullable: true)]
    private $updatedAt;

    #[ORM\OneToMany(targetEntity: 'Reminder', mappedBy: 'category')]
    private $reminders;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'integer', nullable: false, options: ['default' => 0])]
    private $order = 0;

    #[ORM\Column(name: 'fixed_mileage', type: 'boolean', options: ['default' => false])]
    private bool $fixedMileage = false;


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
     * @return ReminderCategory
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
     * Set status.
     *
     * @param string $status
     *
     * @return ReminderCategory
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
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return ReminderCategory
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
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return ReminderCategory
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
     * @param Reminder $reminder
     * @return $this
     */
    public function removeReminder(Reminder $reminder)
    {
        $this->reminders->removeElement($reminder);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAllReminders()
    {
        foreach ($this->reminders as $reminder) {
            $reminder->setCategory(null);
        }
        $this->reminders->clear();

        return $this;
    }

    /**
     * @param Reminder $reminder
     * @return $this
     */
    public function addReminder(Reminder $reminder)
    {
        if (!$this->reminders->contains($reminder)) {
            $reminder->setCategory($this);
            $this->reminders->add($reminder);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRemindersData()
    {
        return array_map(function ($reminder) {
            return $reminder->toArray(['title']);
        }, $this->reminders->toArray());
    }

    /**
     * @return array
     */
    public function getReminderIds(): array
    {
        return $this->reminders->map(
            function (Reminder $reminder) {
                return $reminder->getId();
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getReminders(): array
    {
        return $this->reminders->toArray();
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return ReminderCategory
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
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return ReminderCategory
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

    public function setOrder($order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getFixedMileage(): bool
    {
        return $this->fixedMileage;
    }

    public function setFixedMileage(bool $fixedMileage): self
    {
        $this->fixedMileage = $fixedMileage;

        return $this;
    }
}
