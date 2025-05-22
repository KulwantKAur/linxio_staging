<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use App\Util\ArrayHelper;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceRecord
 */
#[ORM\Table(name: 'service_record')]
#[ORM\Entity(repositoryClass: 'App\Repository\ServiceRecordRepository')]
#[ORM\EntityListeners(['App\EventListener\ServiceRecord\ServiceRecordEntityListener'])]
class ServiceRecord extends BaseEntity
{
    use AttributesTrait;

    public const EDITABLE_FIELDS = [
        'note',
        'cost',
        'status',
        'date',
        'updatedAt',
        'updatedBy',
        'createdBy',
        'odometer',
        'engineHours'
    ];

    public const DEFAULT_FIELDS = [
        'date',
        'formattedDate',
        'reminder',
        'note',
        'cost',
        'files',
        'status',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'repairTitle',
        'type',
        'repairCategory',
        'repairVehicle',
        'repairAsset',
        'odometer',
        'engineHours'
    ];

    public const FIELDS_FOR_REMINDER = [
        'date',
        'note',
        'cost',
        'files',
        'status',
        'createdAt',
        'createdBy',
        'odometer',
        'engineHours'
    ];

    public const SUMMARY_REPORT_FIELDS = [
        'defaultlabel',
        'regno',
        'model',
        'driver_name',
        'groups',
        'depot_name',
        'sr_last_date',
        'sr_count',
        'sr_cost'
    ];

    public const DETAILED_REPORT_FIELDS = [
        'defaultlabel',
        'regno',
        'model',
        'driver_name',
        'groups',
        'depot_name',
        'r_category',
        'sr_date',
        'sr_amount',
        'sr_note'
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const TYPE_SERVICE_RECORD = 'service_record';
    public const TYPE_REPAIR = 'repair';
    public const TYPE_ASSET_REPAIR = 'asset_repair';
    public const LIST_STATUSES = [
        'active'
    ];
    public const ALLOWED_STATUSES = [
        'active',
        'deleted'
    ];

    public function __construct(array $fields)
    {
        $this->files = new ArrayCollection();
        $this->date = $fields['date'];
        $this->cost = ArrayHelper::getValueFromArray($fields, 'cost');
        $this->note = ArrayHelper::getValueFromArray($fields, 'note');
        $this->reminder = ArrayHelper::getValueFromArray($fields, 'reminder');
        $this->createdAt = new \DateTime();
        $this->status = ArrayHelper::getValueFromArray($fields, 'status', self::STATUS_ACTIVE);
        $this->odometer = $fields['odometer'] ?? null;
        $this->engineHours = isset($fields['engineHours']) ? (int)$fields['engineHours'] : null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_FIELDS;
        }

        if (in_array('reminder', $include, true)) {
            $data['reminder'] = $this->getReminderData();
        }
        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate($this->getDate());
        }
        if (in_array('formattedDate', $include, true)) {
            $data['formattedDate'] = $this->getFormattedDate();
        }
        if (in_array('note', $include, true)) {
            $data['note'] = $this->getNote();
        }
        if (in_array('cost', $include, true)) {
            $data['cost'] = $this->getCost();
        }
        if (in_array('files', $include, true)) {
            $data['files'] = $this->getFiles();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('reminderCategory', $include, true)) {
            $data['reminderCategory'] = $this->getReminderCategory();
        }
        if (in_array('reminderTitle', $include, true)) {
            $data['reminderTitle'] = $this->getReminder()->getTitle();
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if ($this->isRepair()) {
            if (in_array('repairTitle', $include, true)) {
                $data['repairTitle'] = $this->getRepairTitle();
            }
            if (in_array('reminderCategory', $include, true)) {
                $data['reminderCategory'] = $this->getRepairCategory();
            }
            if (in_array('repairCategory', $include, true)) {
                $data['repairCategory'] = $this->getRepairCategory();
            }
            if (in_array('repairVehicle', $include, true)) {
                $data['repairVehicle'] = $this->getRepairData()->isVehicleRepair()
                    ? $this->getRepairVehicle()->toArray(Vehicle::DISPLAYED_VALUES)
                    : null;
            }
            if (in_array('repairAsset', $include, true)) {
                $data['repairAsset'] = $this->getRepairData()->isAssetRepair()
                    ? $this->getRepairAsset()->toArray(Vehicle::DISPLAYED_VALUES)
                    : null;
            }
        }
        if (in_array('odometer', $include, true)) {
            $data['odometer'] = $this->getOdometer();
        }
        if (in_array('engineHours', $include, true)) {
            $data['engineHours'] = $this->getEngineHours();
        }

        return $data;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = []): array
    {
        $data = [];

        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate($this->getDate(), self::EXPORT_DATE_FORMAT);
        }
        if (in_array('formattedDate', $include, true)) {
            $data['formattedDate'] = $this->getFormattedDate();
        }
        if ($this->isRepair()) {
            if (in_array('repairTitle', $include, true)) {
                $data['repairTitle'] = $this->getRepairTitle();
            }
            if (in_array('reminderCategory', $include, true)) {
                $data['reminderCategory'] = $this->getRepairCategory();
            }
            if (in_array('repairCategory', $include, true)) {
                $data['repairCategory'] = $this->getRepairCategoryEntity()
                    ? $this->getRepairCategoryEntity()->getName()
                    : null;
            }
            if (in_array('vehicle', $include, true)) {
                $data['vehicle'] = $this->getRepairData()->isVehicleRepair()
                    ? $this->getRepairVehicle()->getRegNo()
                    : null;
            }
            if (in_array('asset', $include, true)) {
                $data['asset'] = $this->getRepairData()->isAssetRepair()
                    ? $this->getRepairAsset()->getName()
                    : null;
            }
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getCreatedBy()->getFullName();
        }
        if (in_array('cost', $include, true)) {
            $data['cost'] = $this->getCost();
        }
        if (in_array('note', $include, true)) {
            $data['note'] = $this->getNote();
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
     * @var \DateTime
     */
    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'note', type: 'text', nullable: true)]
    private $note;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'cost', type: 'float', nullable: true)]
    private $cost;

    /**
     * @var Reminder
     */
    #[ORM\ManyToOne(targetEntity: 'Reminder', inversedBy: 'serviceRecords')]
    #[ORM\JoinColumn(name: 'reminder_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $reminder;

    /**
     * @var RepairData
     */
    #[ORM\ManyToOne(targetEntity: 'RepairData', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repair_data', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $repairData;

    #[ORM\JoinTable(name: 'service_record_file')]
    #[ORM\JoinColumn(name: 'service_record_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'file_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'File')]
    private $files;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: true)]
    private $status;

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

    #[ORM\Column(name: 'odometer', type: 'bigint', nullable: true)]
    private $odometer;

    #[ORM\Column(name: 'engine_hours', type: 'bigint', nullable: true)]
    private $engineHours;

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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return ServiceRecord
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getFormattedDate()
    {
        return $this->date ? Carbon::createFromTimestamp($this->date->getTimestamp())->format(
            self::EXPORT_DATE_FORMAT
        ) : null;
    }

    /**
     * Set note.
     *
     * @param string|null $note
     *
     * @return ServiceRecord
     */
    public function setNote($note = null)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note.
     *
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set cost.
     *
     * @param float|null $cost
     *
     * @return ServiceRecord
     */
    public function setCost($cost = null)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost.
     *
     * @return float|null
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set reminder.
     *
     * @param int $reminder
     *
     * @return ServiceRecord
     */
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }

    /**
     * Get reminder.
     *
     * @return Reminder
     */
    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getReminderData(): ?array
    {
        return $this->reminder ? $this->getReminder()->toArray(Reminder::FIELDS_FOR_SERVICE_RECORD) : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getReminderCategory(): ?array
    {
        return $this->reminder && $this->reminder->getCategory() ? $this->reminder->getCategory()->toArray() : null;
    }

    /**
     * @return ReminderCategory|null
     */
    public function getReminderCategoryEntity(): ?ReminderCategory
    {
        return $this->reminder->getCategory();
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return array_map(
            function (File $file) {
                $file->setPath(LocalFileService::SERVICE_RECORD_PUBLIC_PATH);
                return $file->toArray();
            },
            $this->files->toArray()
        );
    }

    public function removeFiles(array $ids)
    {
        $this->files = array_filter(
            $this->files->toArray(),
            function ($file) use ($ids) {
                return !in_array($file->getId(), $ids);
            }
        );
    }

    /**
     * @param File $file
     */
    public function addFile(File $file)
    {
        $this->files->add($file);
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return ServiceRecord
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
     * @param int $createdBy
     *
     * @return ServiceRecord
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
     * @return ServiceRecord
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
     * @param int|null $updatedBy
     *
     * @return ServiceRecord
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

    /**
     * @return array|null
     */
    public function getUpdatedByData(): ?array
    {
        return $this->updatedBy ? $this->updatedBy->toArray(['id', 'fullName']) : null;
    }

    /**
     * @return int|null
     */
    public function getReminderId()
    {
        return $this->getReminder() ? $this->getReminder()->getId() : null;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return RepairData
     */
    public function getRepairData()
    {
        return $this->repairData;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setRepairTitle(string $title)
    {
        $this->checkRepairEntity();
        $this->repairData->setTitle($title);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRepairTitle()
    {
        return $this->repairData ? $this->repairData->getTitle() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getRepairCategory()
    {
        return $this->repairData && $this->repairData->getCategory()
            ? $this->repairData->getCategory()->toArray()
            : null;
    }

    /**
     * @return ReminderCategory|null
     */
    public function getRepairCategoryEntity()
    {
        return $this->repairData && $this->repairData->getCategory() ? $this->repairData->getCategory() : null;
    }

    /**
     * @param ReminderCategory $category
     * @return $this
     */
    public function setRepairCategory(ReminderCategory $category)
    {
        $this->checkRepairEntity();
        $this->repairData->setCategory($category);

        return $this;
    }

    /**
     * @return $this
     */
    public function setRepairServiceRecord()
    {
        $this->checkRepairEntity();
        $this->repairData->setServiceRecord($this);

        return $this;
    }

    /**
     * @param Vehicle $vehicle
     * @return $this
     */
    public function setRepairVehicle(Vehicle $vehicle)
    {
        $this->checkRepairEntity();
        $this->repairData->setVehicle($vehicle);

        return $this;
    }

    public function setRepairAsset(Asset $asset)
    {
        $this->checkRepairEntity();
        $this->repairData->setAsset($asset);

        return $this;
    }

    /**
     * @return Vehicle
     */
    public function getRepairVehicle()
    {
        return $this->repairData ? $this->repairData->getVehicle() : null;
    }

    public function getRepairAsset()
    {
        return $this->repairData ? $this->repairData->getAsset() : null;
    }

    /**
     * @return Vehicle|null
     */
    public function getServiceRecordVehicle()
    {
        return $this->reminder ? $this->reminder->getVehicle() : null;
    }

    /**
     * @param RepairData $repairData
     * @return $this
     */
    public function setRepair(RepairData $repairData)
    {
        $this->repairData = $repairData;

        return $this;
    }

    /**
     *
     */
    protected function checkRepairEntity()
    {
        if (!$this->repairData) {
            $this->repairData = new RepairData();
            $this->repairData->setServiceRecord($this);
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isServiceRecord()
    {
        return !(bool)$this->getRepairData();
    }

    /**
     * @return bool
     */
    public function isRepair()
    {
        return (bool)$this->getRepairData();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getType()
    {
        if ($this->isServiceRecord()) {
            return self::TYPE_SERVICE_RECORD;
        } elseif ($this->isRepair()) {
            return self::TYPE_REPAIR;
        } elseif ($this->getRepairData()->isAssetRepair()) {
            return self::TYPE_ASSET_REPAIR;
        }
    }

    /**
     * @return Team
     * @throws \Exception
     */
    public function getTeam()
    {
        if ($this->isServiceRecord()) {
            return $this->getReminder()->getTeam();
        } elseif ($this->isRepair()) {
            return $this->getRepairData()->getEntity()->getTeam();
        }
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getTitle()
    {
        if ($this->isServiceRecord()) {
            return $this->getReminder()->getTitle();
        } elseif ($this->isRepair()) {
            return $this->getRepairTitle();
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getReminder() ? $this->getReminder()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    public function getVehicle(): ?BaseEntity
    {
        if ($this->isServiceRecord()) {
            return $this->getReminder()->getVehicle();
        } elseif ($this->isRepair()) {
            return $this->getRepairVehicle();
        }

        return null;
    }

    public function getAsset(): ?BaseEntity
    {
        if ($this->isServiceRecord()) {
            return $this->getReminder()->getAsset();
        } elseif ($this->isRepair()) {
            return $this->getRepairAsset();
        }

        return null;
    }

    public function getEntityString(): ?string
    {
        if ($this->getVehicle()) {
            return $this->getVehicle()->getRegNo();
        } elseif ($this->getAsset()) {
            return $this->getAsset()->getName();
        }

        return null;
    }

    public function getOdometer(): ?float
    {
        $value = $this->odometer ?? $this->getReminder()?->getCurrentOdometer();

        return $value ? floatval($value) : null;
    }

    public function setOdometer(null|int $odometer): self
    {
        $this->odometer = $odometer;

        return $this;
    }

    public function setEngineHours($hours = null): self
    {
        $this->engineHours = $hours;

        return $this;
    }

    public function getEngineHours(): ?int
    {
        return $this->engineHours;
    }
}
