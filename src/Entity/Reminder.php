<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use App\Util\DateHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reminder
 */
#[ORM\Table(name: 'reminder')]
#[ORM\Entity(repositoryClass: 'App\Repository\ReminderRepository')]
#[ORM\EntityListeners(['App\EventListener\Reminder\ReminderEntityListener'])]
class Reminder extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'title',
        'vehicle',
        'asset',
        'team',
        'status',
        'date',
        'datePeriod',
        'dateNotification',
        'mileage',
        'mileagePeriod',
        'mileageNotification',
        'hours',
        'hoursPeriod',
        'hoursNotification',
        'note',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'controlDate',
        'remainingDays',
        'controlMileage',
        'remainingMileage',
        'controlHours',
        'remainingHours',
        'serviceRecords',
        'draftRecord',
        'category',
        'dateCheckbox',
        'mileageCheckbox',
        'hoursCheckbox'
    ];

    public const FIELDS_FOR_SERVICE_RECORD = [
        'title',
        'team',
        'status',
        'date',
        'datePeriod',
        'dateNotification',
        'mileage',
        'mileagePeriod',
        'mileageNotification',
        'hours',
        'hoursPeriod',
        'hoursNotification',
        'note',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'controlDate',
        'remainingDays',
        'controlMileage',
        'remainingMileage',
        'controlHours',
        'remainingHours',
        'category',
        'dateCheckbox',
        'mileageCheckbox',
        'hoursCheckbox'
    ];

    public const REPORT_DISPLAY_VALUES = [
        'vehicleDefaultLabel',
        'vehicleRegNo',
        'vehicleModel',
        'driver',
        'vehicleGroups',
        'vehicleDepot',
        'category',
        'status',
        'remainingDays',
        'remainingMileage',
        'remainingHours',
        'note',
        'updatedAt',
        'createdAt'
    ];

    public const VEHICLE_REPORT_DISPLAY_VALUES = [
        'category',
        'status',
        'remainingDays',
        'remainingMileage',
        'remainingHours',
        'note',
        'updatedAt',
        'createdAt',
        'serviceRecords'
    ];

    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;
    public const STATUS_DUE_SOON = 'due_soon';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DONE = 'done';
    public const STATUS_DRAFT = 'draft';

    public const VEHICLE_TYPE = 'vehicle';
    public const ASSET_TYPE = 'asset';

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DUE_SOON,
        self::STATUS_EXPIRED,
        self::STATUS_DONE,
        self::STATUS_DELETED,
        self::STATUS_DRAFT,
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DUE_SOON,
        self::STATUS_EXPIRED,
        self::STATUS_DONE,
        self::STATUS_DRAFT
    ];

    public const DASHBOARD_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DUE_SOON,
        self::STATUS_EXPIRED
    ];

    /**
     * Reminder constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->asset = $fields['asset'] ?? null;
        $this->title = $fields['title'];
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->date = $fields['date'] ?? null;
        $this->datePeriod = $fields['datePeriod'] ?? null;
        $this->dateNotification = $fields['dateNotification'] ?? null;
        $this->mileage = $fields['mileage'] ?? null;
        $this->mileagePeriod = $fields['mileagePeriod'] ?? null;
        $this->mileageNotification = $fields['mileageNotification'] ?? null;
        $this->hours = $fields['hours'] ?? null;
        $this->hoursPeriod = $fields['hoursPeriod'] ?? null;
        $this->hoursNotification = $fields['hoursNotification'] ?? null;
        $this->note = $fields['note'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->serviceRecords = new ArrayCollection();
        $this->category = $fields['category'] ?? null;
        $this->dateCheckbox = $fields['dateCheckbox'] ?? false;
        $this->mileageCheckbox = $fields['mileageCheckbox'] ?? false;
        $this->hoursCheckbox = $fields['hoursCheckbox'] ?? false;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('title', $include, true)) {
            $data['title'] = $this->getTitle();
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray(Vehicle::REMINDER_VALUES);
        }
        if (in_array('vehicleDetailed', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray(Vehicle::LIST_DISPLAY_VALUES);
        }
        if (in_array('vehicleRegNo', $include, true)) {
            $data['vehicleRegNo'] = $this->getVehicle()?->getRegNo();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeamData();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate($this->getDate());
        }
        if (in_array('datePeriod', $include, true)) {
            $data['datePeriod'] = $this->getDatePeriod();
        }
        if (in_array('dateNotification', $include, true)) {
            $data['dateNotification'] = $this->getDateNotification();
        }
        if (in_array('mileage', $include, true)) {
            $data['mileage'] = $this->getMileage();
        }
        if (in_array('mileagePeriod', $include, true)) {
            $data['mileagePeriod'] = $this->getMileagePeriod();
        }
        if (in_array('mileageNotification', $include, true)) {
            $data['mileageNotification'] = $this->getMileageNotification();
        }
        if (in_array('hours', $include, true)) {
            $data['hours'] = $this->getHours();
        }
        if (in_array('hoursPeriod', $include, true)) {
            $data['hoursPeriod'] = $this->getHoursPeriod();
        }
        if (in_array('hoursNotification', $include, true)) {
            $data['hoursNotification'] = $this->getHoursNotification();
        }
        if (in_array('note', $include, true)) {
            $data['note'] = $this->getNote();
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
        if (in_array('controlDate', $include, true)) {
            $data['controlDate'] = $this->formatDate($this->getDate());
        }
        if (in_array('remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getRemainingDays();
        }
        if (in_array('controlMileage', $include, true)) {
            $data['controlMileage'] = $this->getMileage();
        }
        if (in_array('remainingMileage', $include, true)) {
            $data['remainingMileage'] = $this->getRemainingMileage();
        }
        if (in_array('controlHours', $include, true)) {
            $data['controlHours'] = $this->getHours();
        }
        if (in_array('remainingHours', $include, true)) {
            $data['remainingHours'] = $this->getRemainingHours();
        }
        if (in_array('serviceRecords', $include, true)) {
            if ($include['serviceStartDate'] ?? null && $include['serviceEndDate'] ?? null) {
                $data['serviceRecords'] =
                    $this->getServiceRecords($include['serviceStartDate'], $include['serviceEndDate']);
            } else {
                $data['serviceRecords'] = $this->getServiceRecords();
            }
        }
        if (in_array('draftRecord', $include, true)) {
            $data['draftRecord'] = $this->getDraftRecordData();
        }
        if (in_array('category', $include, true)) {
            $data['category'] = $this->getCategoryData();
        }
        if (in_array('dateCheckbox', $include, true)) {
            $data['dateCheckbox'] = $this->getDateCheckbox();
        }
        if (in_array('mileageCheckbox', $include, true)) {
            $data['mileageCheckbox'] = $this->getMileageCheckbox();
        }
        if (in_array('hoursCheckbox', $include, true)) {
            $data['hoursCheckbox'] = $this->getHoursCheckbox();
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset()?->toArray();
        }
        if (in_array('vehicleGroups', $include, true)) {
            $data['vehicleGroups'] = $this->getVehicle()?->getGroupsArray();
        }
        if (in_array('vehicleDepot', $include, true)) {
            $data['vehicleDepot'] = $this->getVehicle()?->getDepot()?->toArray();
        }

        return $data;
    }

    public function toExport(array $include = [], ?User $currentUser = null): array
    {
        $data = [];
        if (in_array('vehicleName', $include, true)) {
            $data['vehicleName'] = $this->getVehicle()?->getDefaultLabel();
        }
        if (in_array('defaultLabel', $include, true)) {
            $data['defaultLabel'] = $this->getVehicle()?->getDefaultLabel();
        }
        if (in_array('vehicleRegNo', $include, true)) {
            $data['vehicleRegNo'] = $this->getVehicle()?->getRegNo();
        }
        if (in_array('regno', $include, true)) {
            $data['regno'] = $this->getVehicle()?->getRegNo();
        }
        if (in_array('title', $include, true)) {
            $data['title'] = $this->getTitle();
        }
        if (in_array('vehicleModel', $include, true)) {
            $data['vehicleModel'] = $this->getVehicle()?->getModel();
        }
        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getVehicle()?->getDriverName();
        }
        if (in_array('vehicleGroups', $include, true)) {
            $data['vehicleGroups'] = $this->getVehicle()?->getGroupsString();
        }
        if (in_array('vehicleDepot', $include, true)) {
            $data['vehicleDepot'] = $this->getVehicle()?->getDepot()?->getName();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('categoryId', $include, true)) {
            $data['categoryId'] = $this->getCategory()?->getName();
        }
        if (in_array('categoryName', $include, true)) {
            $data['categoryName'] = $this->getCategory()?->getName();
        }
        if (in_array('remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getRemainingDays();
        }
        if (in_array('remainingMileage', $include, true)) {
            $data['remainingMileage'] = $this->getRemainingMileage();
        }
        if (in_array('remainingHours', $include, true)) {
            $data['remainingHours'] = $this->getRemainingHours();
        }
        if (in_array('mileage', $include, true)) {
            $data['mileage'] = $this->getMileage();
        }
        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate($this->getDate(), BaseEntity::EXPORT_DATE_FORMAT);
        }
        if (in_array('controlDate', $include, true)) {
            $data['controlDate'] = $this->formatDate(
                $this->getDate(), BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT, $currentUser->getTimezone());
        }
        if (in_array('controlMileage', $include, true)) {
            $data['controlMileage'] = $this->getMileage();
        }
        if (in_array('hours', $include, true)) {
            $data['hours'] = $this->getHours();
        }
        if (in_array('controlHours', $include, true)) {
            $data['controlHours'] = $this->getHours();
        }
        if (in_array('notes', $include, true)) {
            $data['notes'] = $this->getNote();
        }
        if (in_array('lastModified', $include, true)) {
            $data['lastModifiedDate'] = $this->formatDate(
                $this->getUpdatedAt(),
                $currentUser->getDateFormatSettingConverted(),
                $currentUser->getTimezone()
            );
            $data['lastModifiedTime'] = $this->formatDate(
                $this->getUpdatedAt(),
                self::EXPORT_TIME_FORMAT,
                $currentUser->getTimezone()
            );
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAtDate'] = $this->formatDate(
                $this->getCreatedAt(),
                $currentUser->getDateFormatSettingConverted(),
                $currentUser->getTimezone()
            );
            $data['createdAtTime'] = $this->formatDate(
                $this->getCreatedAt(),
                self::EXPORT_TIME_FORMAT,
                $currentUser->getTimezone()
            );
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset()?->getName();
        }
        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getVehicle()?->getGroupsString();
        }
        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->getVehicle()?->getDepot()?->getName();
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
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'reminders')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var Asset
     */
    #[ORM\ManyToOne(targetEntity: 'Asset', inversedBy: 'reminders')]
    #[ORM\JoinColumn(name: 'asset_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $asset;

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
     * @var boolean
     */
    #[ORM\Column(name: 'date_checkbox', type: 'boolean', nullable: false, options: ['default' => false])]
    private $dateCheckbox;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'date_period', type: 'bigint', nullable: true)]
    private $datePeriod;

    /**
     * @var int
     */
    #[ORM\Column(name: 'date_notification', type: 'integer', nullable: true)]
    private $dateNotification;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'mileage_checkbox', type: 'boolean', nullable: false, options: ['default' => false])]
    private $mileageCheckbox;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'mileage', type: 'bigint', nullable: true)]
    private $mileage;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'mileage_period', type: 'bigint', nullable: true)]
    private $mileagePeriod;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'mileage_notification', type: 'bigint', nullable: true)]
    private $mileageNotification;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'hours_checkbox', type: 'boolean', nullable: false, options: ['default' => false])]
    private $hoursCheckbox;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'hours', type: 'integer', nullable: true)]
    private $hours;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'hours_period', type: 'bigint', nullable: true)]
    private $hoursPeriod;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'hours_notification', type: 'integer', nullable: true)]
    private $hoursNotification;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'note', type: 'text', nullable: true)]
    private $note;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int
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

    #[ORM\OneToMany(targetEntity: 'ServiceRecord', mappedBy: 'reminder')]
    private $serviceRecords;

    /**
     * @var DocumentRecord
     */
    private $draftRecord;

    /**
     * @var null|ReminderCategory
     */
    #[ORM\ManyToOne(targetEntity: 'ReminderCategory', inversedBy: 'reminders')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $category;


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
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->getEntity()->getTeam()->getId();
    }

    /**
     * @return array
     */
    public function getTeamData(): array
    {
        return $this->getEntity()->getTeam()->toArray();
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->getEntity()->getTeam();
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return Reminder
     */
    public function setVehicle(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function getDevice(): ?Device
    {
        return $this->getVehicle() ? $this->getVehicle()->getDevice() : null;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Reminder
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Reminder
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
     * @return string
     */
    public function getFormattedStatus()
    {
        return ucfirst($this->status);
    }

    /**
     * Set date.
     *
     * @param \DateTime|null $date
     *
     * @return Reminder
     */
    public function setDate($date = null)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    public function getFormattedDate()
    {
        return $this->date
            ? Carbon::createFromTimestamp($this->date->getTimestamp())->format(self::EXPORT_DATE_FORMAT)
            : null;
    }

    /**
     * @return bool|\DateInterval
     * @throws \Exception
     */
    public function getRemainingDays()
    {
        if ($this->date && $this->getDateCheckbox()) {
            return DateHelper::getDaysCountBeforeDate($this->date);
        }

        return null;
    }

    /**
     * @return float|int|mixed|null
     */
    public function getRemainingMileage()
    {
        $odometer = $this->getCurrentOdometer();

        if ($this->mileage && $odometer && $this->getMileageCheckbox()) {
            return $this->mileage - $odometer;
        } else {
            return null;
        }
    }

    public function getCurrentOdometer()
    {
        $lastTROdometer = $this->getVehicle()?->getLastTrackerRecordOdometer();

        return $this->getVehicle()?->getLastOdometerValue($lastTROdometer);
    }

    /**
     * @return int|mixed|null
     */
    public function getRemainingHours()
    {
        $engineHours = $this->getVehicle() && $this->getVehicle()->getEngineOnTime() ? $this->getVehicle()->getEngineOnTime() / 3600 : 0;

        if ($this->hours && $engineHours && $this->getHoursCheckbox()) {
            return ($this->hours - $engineHours) >= 0 ? round($this->hours - $engineHours, 1) : 0;
        } else {
            return null;
        }
    }

    /**
     * Set datePeriod.
     *
     * @param int|null $datePeriod
     *
     * @return Reminder
     */
    public function setDatePeriod($datePeriod = null)
    {
        $this->datePeriod = $datePeriod;

        return $this;
    }

    /**
     * Get datePeriod.
     *
     * @return int|null
     */
    public function getDatePeriod()
    {
        return (int)$this->datePeriod;
    }

    /**
     * Set dateNotification.
     *
     * @param int $dateNotification
     *
     * @return Reminder
     */
    public function setDateNotification($dateNotification)
    {
        $this->dateNotification = $dateNotification;

        return $this;
    }

    /**
     * Get dateNotification.
     *
     * @return int
     */
    public function getDateNotification()
    {
        return (int)$this->dateNotification;
    }

    /**
     * Set mileage.
     *
     * @param int|null $mileage
     *
     * @return Reminder
     */
    public function setMileage($mileage = null)
    {
        $this->mileage = $mileage;

        return $this;
    }

    /**
     * Get mileage.
     *
     * @return int|null
     */
    public function getMileage()
    {
        return !is_null($this->mileage) ? (int)$this->mileage : null;
    }

    /**
     * Set mileagePeriod.
     *
     * @param int|null $mileagePeriod
     *
     * @return Reminder
     */
    public function setMileagePeriod($mileagePeriod = null)
    {
        $this->mileagePeriod = $mileagePeriod;

        return $this;
    }

    /**
     * Get mileagePeriod.
     *
     * @return int|null
     */
    public function getMileagePeriod()
    {
        return (int)$this->mileagePeriod;
    }

    /**
     * Set mileageNotification.
     *
     * @param int|null $mileageNotification
     *
     * @return Reminder
     */
    public function setMileageNotification($mileageNotification = null)
    {
        $this->mileageNotification = $mileageNotification;

        return $this;
    }

    /**
     * Get mileageNotification.
     *
     * @return int|null
     */
    public function getMileageNotification()
    {
        return (int)$this->mileageNotification;
    }

    /**
     * Set hours.
     *
     * @param int|null $hours
     *
     * @return Reminder
     */
    public function setHours($hours = null)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours.
     *
     * @return int|null
     */
    public function getHours()
    {
        return !is_null($this->hours) ? (int)$this->hours : null;
    }

    /**
     * Set hoursPeriod.
     *
     * @param int|null $hoursPeriod
     *
     * @return Reminder
     */
    public function setHoursPeriod($hoursPeriod = null)
    {
        $this->hoursPeriod = $hoursPeriod;

        return $this;
    }

    /**
     * Get hoursPeriod.
     *
     * @return int|null
     */
    public function getHoursPeriod()
    {
        return (int)$this->hoursPeriod;
    }

    /**
     * Set hoursNotification.
     *
     * @param int|null $hoursNotification
     *
     * @return Reminder
     */
    public function setHoursNotification($hoursNotification = null)
    {
        $this->hoursNotification = $hoursNotification;

        return $this;
    }

    /**
     * Get hoursNotification.
     *
     * @return int|null
     */
    public function getHoursNotification()
    {
        return (int)$this->hoursNotification;
    }

    /**
     * Set note.
     *
     * @param string|null $note
     *
     * @return Reminder
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
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Reminder
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
     * @return Reminder
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return Reminder
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
     * @param User|null $updatedBy
     *
     * @return Reminder
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
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function getUpdatedByData(): ?array
    {
        return $this->updatedBy ? $this->updatedBy->toArray(['id', 'fullName']) : null;
    }

    /**
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null $reminderCategory
     * @return array
     */
    public function getServiceRecords(?Carbon $startDate = null, ?Carbon $endDate = null, $reminderCategory = null)
    {
        if ($startDate && $endDate) {
            $startDate->setTimezone('UTC');
            $endDate->setTimezone('UTC');
            $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->neq('status', ServiceRecord::STATUS_DRAFT))
                ->andWhere(Criteria::expr()->neq('status', ServiceRecord::STATUS_DELETED))
                ->andWhere(Criteria::expr()->gte('date', $startDate))
                ->andWhere(Criteria::expr()->lte('date', $endDate))
                ->orderBy(['id' => Criteria::DESC]);
            if ($reminderCategory && (($this->getCategory()
                        && $this->getCategory()->getId() !== (int)$reminderCategory) || !$this->getCategory())) {
                return [];
            }
            $serviceRecords = $this->serviceRecords->matching($criteria);
        } else {
            $serviceRecords = $this->serviceRecords->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->neq('status', ServiceRecord::STATUS_DRAFT))
                    ->andWhere(Criteria::expr()->neq('status', ServiceRecord::STATUS_DELETED))
                    ->orderBy(['id' => Criteria::DESC])
            );
        }

        $result = [];
        foreach ($serviceRecords as $serviceRecord) {
            $result[] = $serviceRecord->toArray(
                array_merge(ServiceRecord::FIELDS_FOR_REMINDER, ['reminderCategory', 'reminderTitle'])
            );
        }

        return $result;
    }

    /**
     * @return ArrayCollection
     */
    public function getServiceRecordEntities()
    {
        return $this->serviceRecords;
    }

    /**
     * @return ArrayCollection
     */
    public function getActiveServiceRecords()
    {
        return $this->serviceRecords->filter(
            function ($serviceRecord) {
                return $serviceRecord->isActive();
            }
        );
    }

    /**
     * @param ServiceRecord $serviceRecord
     * @return $this
     */
    public function removeServiceRecord(ServiceRecord $serviceRecord)
    {
        $this->serviceRecords->removeElement($serviceRecord);

        return $this;
    }

    /**
     * @param ServiceRecord $serviceRecord
     * @return $this
     */
    public function addServiceRecord(ServiceRecord $serviceRecord)
    {
        $this->serviceRecords->add($serviceRecord);

        return $this;
    }

    /**
     * @return ServiceRecord
     */
    public function getDraftRecord(): ?ServiceRecord
    {
        return $this->getServiceRecordEntities()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('status', ServiceRecord::STATUS_DRAFT))
                ->orderBy(['id' => Criteria::DESC])
        )->first() ?: null;
    }

    public function getDraftRecordData(): ?array
    {
        $draftRecord = $this->getDraftRecord();

        return $draftRecord ? $draftRecord->toArray(ServiceRecord::FIELDS_FOR_REMINDER) : null;
    }

    /**
     * @param ReminderCategory|null $reminderCategory
     * @return $this
     */
    public function setCategory(?ReminderCategory $reminderCategory)
    {
        if (is_null($reminderCategory) && $this->category) {
            $this->category->removeReminder($this);
        }

        $this->category = $reminderCategory;

        return $this;
    }

    /**
     * @return ReminderCategory|null
     */
    public function getCategory(): ?ReminderCategory
    {
        return $this->category;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCategoryData()
    {
        return $this->category ? $this->category->toArray(ReminderCategory::LIST_DISPLAY_VALUES) : null;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDateCheckbox($value)
    {
        $this->dateCheckbox = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDateCheckbox()
    {
        return $this->dateCheckbox;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMileageCheckbox($value)
    {
        $this->mileageCheckbox = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMileageCheckbox()
    {
        return $this->mileageCheckbox;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setHoursCheckbox($value)
    {
        $this->hoursCheckbox = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHoursCheckbox()
    {
        return $this->hoursCheckbox;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getEntity()->getTimeZoneName();
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function isVehicleReminder()
    {
        return (bool)$this->getVehicle();
    }

    public function isAssetReminder()
    {
        return (bool)$this->getAsset();
    }

    public function getEntity(): ?BaseEntity
    {
        if ($this->getVehicle()) {
            return $this->getVehicle();
        } elseif ($this->getAsset()) {
            return $this->getAsset();
        } else {
            return null;
        }
    }

    public function getType(): ?string
    {
        if ($this->isVehicleReminder()) {
            return self::VEHICLE_TYPE;
        } elseif ($this->isAssetReminder()) {
            return self::ASSET_TYPE;
        }

        return null;
    }

    public function getLastActiveRecord(): ?ServiceRecord
    {
        return $this->getServiceRecordEntities()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('status', ServiceRecord::STATUS_ACTIVE))
                ->orderBy(['date' => Criteria::DESC])
        )->first() ?: null;
    }

    public function isFixedMileage(): bool
    {
        return (bool)$this->getCategory()?->getFixedMileage();
    }

    public function getDepot(): ?Depot
    {
        return $this->getVehicle()?->getDepot();
    }

    public function getGroups(): ?ArrayCollection
    {
        return $this->getVehicle()->getGroups();
    }
}
