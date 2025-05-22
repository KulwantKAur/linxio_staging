<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Document
 */
#[ORM\Table(name: 'document')]
#[ORM\Entity(repositoryClass: 'App\Repository\DocumentRepository')]
#[ORM\EntityListeners(['App\EventListener\Vehicle\Document\DocumentEntityListener'])]
#[ORM\HasLifecycleCallbacks]
class Document extends BaseEntity
{
    use AttributesTrait;

    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;
    public const STATUS_DRAFT = 'draft';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_EXPIRE_SOON = 'expire_soon';
    public const VEHICLE_DOCUMENT = 'vehicleDocument';
    public const DRIVER_DOCUMENT = 'driverDocument';
    public const ASSET_DOCUMENT = 'assetDocument';

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED,
        self::STATUS_DRAFT,
        self::STATUS_EXPIRED,
        self::STATUS_EXPIRE_SOON,
        self::STATUS_ARCHIVE,
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DRAFT,
        self::STATUS_EXPIRED,
        self::STATUS_EXPIRE_SOON
    ];

    public const DASHBOARD_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_EXPIRE_SOON
    ];

    public const EDITABLE_FIELDS = [
        'title',
        'updatedAt',
        'updatedBy',
        'notifyBefore',
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'title',
        'status',
        'vehicle',
        'driver',
        'asset',
        'issueDate',
        'expDate',
        'notifyBefore',
        'cost',
        'note',
        'records',
        'files',
        'team',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'documentType',
        'noExpiry',
    ];

    public const DEFAULT_LISTING_DISPLAY_VALUES = [
        'id',
        'title',
        'status',
        'remainingDays',
        'expDate',
        'issueDate',
        'depot',
        'groups'
    ];

    public const REPORT_DISPLAY_VALUES = [
        'title',
        'status',
        'regNo',
        'remainingDays',
        'issueDate',
        'expDate',
        'driver'
    ];

    /**
     * @param array $fields
     * @return array
     */
    public static function prepareListFields(array $fields)
    {
        $filtered = array_map(
            static function (string $field) {
                return in_array($field, DocumentRecord::DATA_FIELDS, true)
                    ? sprintf('activeRecord.%s', $field)
                    : $field;
            },
            $fields
        );

        return array_merge($filtered, $fields);
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('title', $include, true)) {
            $data['title'] = $this->title;
        }

        if (in_array('vehicleId', $include, true) && $this->isVehicleDocument()) {
            $data['vehicleId'] = $this->getVehicleId();
        }

        if (in_array('driverId', $include, true) && $this->isDriverDocument()) {
            $data['driverId'] = $this->getDriver()->getId();
        }

        if (in_array('assetId', $include, true) && $this->isAssetDocument()) {
            $data['assetId'] = $this->getAsset()->getId();
        }

        if (in_array('documentType', $include, true)) {
            $data['documentType'] = $this->getDocumentType();
        }

        if (in_array('vehicle', $include, true) && $this->isVehicleDocument()) {
            $data['vehicle'] = $this->getVehicleArray();
        }

        if (in_array('driver', $include, true) && $this->isDriverDocument()) {
            $data['driver'] = $this->getDriverArray();
        }

        if (in_array('asset', $include, true) && $this->isAssetDocument()) {
            $data['asset'] = $this->getAssetArray();
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

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getCommonDocumentStatus();
        }

        if (in_array('issueDate', $include, true)) {
            $data['issueDate'] = $this->formatDate($this->getCurrentActiveRecordField('issueDate'));
        }

        if (in_array('expDate', $include, true)) {
            $data['expDate'] = $this->formatDate($this->getCurrentActiveRecordField('expDate'));
        }

        if (in_array('notifyBefore', $include, true)) {
            $data['notifyBefore'] = $this->notifyBefore;
        }

        if (in_array('cost', $include, true)) {
            $data['cost'] = $this->getCurrentActiveRecordField('cost');
        }

        if (in_array('note', $include, true)) {
            $data['note'] = $this->getCurrentActiveRecordField('note');
        }

        if (in_array('remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getCurrentActiveRecordField('remainingDays');
        }

        if (in_array('files', $include, true)) {
            $data['files'] = $this->getCurrentActiveRecordField('filesArray');
        }

        if (in_array('filesIds', $include, true)) {
            $data['filesIds'] = $this->getCurrentActiveRecordField('filesArrayIds');
        }

        if (in_array('records', $include, true)) {
            $data['records'] = $this->getHistoryRecordsArray();
        }

        if (in_array('activeRecord.issueDate', $include, true)) {
            $data['issueDate'] = $this->formatDate($this->getCurrentActiveRecordField('issueDate'));
        }

        if (in_array('activeRecord.expDate', $include, true)) {
            $data['expDate'] = $this->formatDate($this->getCurrentActiveRecordField('expDate'));
        }

        if (in_array('activeRecord.cost', $include, true)) {
            $data['cost'] = $this->getCurrentActiveRecordField('cost');
        }

        if (in_array('activeRecord.note', $include, true)) {
            $data['note'] = $this->getCurrentActiveRecordField('note');
        }

        if (in_array('activeRecord.remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getCurrentActiveRecordField('remainingDays');
        }
        if (in_array('vehicleRegNo', $include, true)) {
            $data['vehicleRegNo'] = $this->getVehicleRegNo();
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeamArray();
        }
        if (in_array('noExpiry', $include, true)) {
            $data['noExpiry'] = $this->getNoExpiry();
        }
        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->getVehicleDepot()?->toArray(['name']);
        }
        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getVehicleGroupsArray();
        }

        return $data;
    }

    public function __construct(array $fields)
    {
        $this->title = $fields['title'];
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->driver = $fields['driver'] ?? null;
        $this->asset = $fields['asset'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->records = new ArrayCollection();
        $this->notifyBefore = $fields['notifyBefore'] ?? null;
    }

    /**
     * @param array $include
     * @param User|null $currentUser
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $currentUser = null): array
    {
        $data = [];

        if (in_array('regNo', $include, true)) {
            $data['regNo'] = $this->getVehicleRegNo();
        }

        if (in_array('fullName', $include, true)) {
            $data['fullName'] = $this->getDriver()?->getFullName();
        }

        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver()?->getFullName();
        }

        if (in_array('title', $include, true)) {
            $data['title'] = $this->title;
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getCommonDocumentStatus();
        }

        if (in_array('remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getCurrentActiveRecordField('remainingDays');
        }

        if (in_array('issueDate', $include, true)) {
            $data['issueDate'] = $this->formatDate(
                $this->getCurrentActiveRecordField('issueDate'),
                self::EXPORT_DATE_WITHOUT_TIME_FORMAT,
                $currentUser->getTimezone()
            );
        }

        if (in_array('expDate', $include, true)) {
            $data['expDate'] = $this->formatDate(
                $this->getCurrentActiveRecordField('expDate'),
                self::EXPORT_DATE_WITHOUT_TIME_FORMAT,
                $currentUser->getTimezone()
            );
        }

        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->getVehicleDepot()?->getName();
        }

        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getVehicle()?->getGroupsString();
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
    private $status = self::STATUS_ACTIVE;

    /**
     * @var ArrayCollection|DocumentRecord[]
     */
    #[ORM\OneToMany(targetEntity: 'DocumentRecord', mappedBy: 'document')]
    private $records;

    /**
     * @var DocumentRecord
     */
    private $draftRecord;

    /**
     * @var DocumentRecord
     */
    private $currentActiveRecord;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'notify_before', type: 'integer', nullable: true)]
    private $notifyBefore;

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
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var Asset
     */
    #[ORM\ManyToOne(targetEntity: 'Asset')]
    #[ORM\JoinColumn(name: 'asset_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $asset;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Document
     */
    public function setTitle(string $title): Document
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
     * @return bool
     */
    public function isDeleted(): bool
    {
        return self::STATUS_DELETED === $this->status;
    }

    /**
     * @return string
     */
    public function getCommonDocumentStatus(): string
    {
        if (in_array($this->status, [self::STATUS_DELETED, self::STATUS_ARCHIVE])) {
            return $this->status;
        }

        if (null !== $this->getCurrentActiveRecord()) {
            return $this->getCurrentActiveRecord()->getStatus();
        }

        return DocumentRecord::STATUS_DRAFT;
    }

    /**
     * @param string $status
     * @return Document
     */
    public function setStatus(string $status): Document
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return DocumentRecord[]|ArrayCollection
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return DocumentRecord[]|ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getHistoryRecords()
    {
        return $this->records->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('status', DocumentRecord::STATUS_DRAFT))
                ->orderBy(['id' => Criteria::DESC])
        );
    }

    /**
     * @return array
     */
    public function getHistoryRecordsArray(): array
    {
        return array_map(
            static function (DocumentRecord $dr) {
                return $dr->toArray();
            },
            $this->getHistoryRecords()->toArray()
        );
    }

    /**
     * @param $records
     * @return Document
     */
    public function setRecords($records): Document
    {
        $this->records = $records;

        return $this;
    }

    /**
     * @param DocumentRecord $documentRecord
     * @return Document
     */
    public function addRecord(DocumentRecord $documentRecord): Document
    {
        $this->records->add($documentRecord);
        $documentRecord->setDocument($this);

        return $this;
    }

    /**
     * @return DocumentRecord|null
     */
    public function getCurrentActiveRecord(): ?DocumentRecord
    {
        return $this->currentActiveRecord;
    }

    /**
     * @param DocumentRecord|null $currentActiveRecord
     * @return Document
     */
    public function setCurrentActiveRecord(?DocumentRecord $currentActiveRecord): Document
    {
        $this->currentActiveRecord = $currentActiveRecord;

        return $this;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getCurrentActiveRecordField($name)
    {
        $getter = sprintf('get%s', ucfirst($name));

        return $this->currentActiveRecord && method_exists($this->currentActiveRecord, $getter)
            ? $this->currentActiveRecord->$getter()
            : null;
    }

    /**
     * @return mixed|null
     */
    public function getRemainingDays()
    {
        return $this->getCurrentActiveRecordField('remainingDays');
    }

    /**
     * @return Vehicle
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getVehicleArray(): ?array
    {
        return $this->vehicle ? $this->vehicle->toArray(['type', 'model', 'regNo']) : null;
    }

    public function getDriverArray(): ?array
    {
        return $this->driver ? $this->driver->toArray(User::SIMPLE_VALUES) : null;
    }

    public function getAssetArray(): ?array
    {
        return $this->getAsset() ? $this->getAsset()->toArray() : null;
    }

    /**
     * @return string|null
     */
    public function getRegNo()
    {
        return $this->vehicle ? $this->vehicle->getRegNo() : null;
    }

    /**
     * @param Vehicle $vehicle
     * @return Document
     */
    public function setVehicle(Vehicle $vehicle): Document
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotifyBefore(): ?int
    {
        return $this->notifyBefore;
    }

    /**
     * @param int|null $notifyBefore
     */
    public function setNotifyBefore(?int $notifyBefore): void
    {
        $this->notifyBefore = $notifyBefore;
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
     * @return Document
     */
    public function setCreatedAt(\DateTime $createdAt): Document
    {
        $this->createdAt = $createdAt;

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
     * @return int|null
     */
    public function getCreatedById(): ?int
    {
        return $this->createdBy ? $this->getCreatedBy()->getId() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCreatedByArray(): ?array
    {
        return $this->createdBy ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param User $createdBy
     * @return Document
     */
    public function setCreatedBy(User $createdBy): Document
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return Document
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Document
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy(): ?User
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
     * @param User|null $updatedBy
     * @return Document
     */
    public function setUpdatedBy(?User $updatedBy): Document
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->getVehicle() ? $this->getVehicle()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getVehicleRegNo()
    {
        return $this->getVehicle() ? $this->getVehicle()->getRegNo() : null;
    }

    /**
     * @return User
     */
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @param User $driver
     * @return $this
     */
    public function setDriver(User $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam(): ?Team
    {
        if ($this->getVehicle()) {
            return $this->getVehicle()->getTeam();
        } elseif ($this->getDriver()) {
            return $this->getDriver()->getTeam();
        } elseif ($this->getAsset()) {
            return $this->getAsset()->getTeam();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getDocumentType()
    {
        if ($this->getVehicle()) {
            return self::VEHICLE_DOCUMENT;
        } elseif ($this->getDriver()) {
            return self::DRIVER_DOCUMENT;
        } elseif ($this->getAsset()) {
            return self::ASSET_DOCUMENT;
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getTeamArray(): ?array
    {
        return $this->getTeam() ? $this->getTeam()->toArray() : null;
    }

    /**
     * @return bool
     */
    public function isDriverDocument()
    {
        return $this->getDocumentType() === self::DRIVER_DOCUMENT;
    }

    /**
     * @return bool
     */
    public function isVehicleDocument()
    {
        return $this->getDocumentType() === self::VEHICLE_DOCUMENT;
    }

    /**
     * @return bool
     */
    public function isAssetDocument()
    {
        return $this->getDocumentType() === self::ASSET_DOCUMENT;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getVehicle() ? $this->getVehicle()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
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

    public function getNoExpiry(): bool
    {
        return $this->getCurrentActiveRecord()?->getNoExpiry() ?? false;
    }

    public function refreshCurrentActiveRecord()
    {
        $this->setCurrentActiveRecord(
            $this->getRecords()->matching(
                Criteria::create()
                    ->where(Criteria::expr()->neq('status', DocumentRecord::STATUS_DRAFT))
                    ->orderBy(['id' => Criteria::DESC])
            )->first() ?: null
        );
    }

    public function getFullName(): ?string
    {
        return $this->getDriver()?->getFullName();
    }

    public function getVehicleDepot(): ?Depot
    {
        return $this->getVehicle()?->getDepot();
    }

    public function getVehicleGroups(): ?ArrayCollection
    {
        return $this->getVehicle()?->getGroups();
    }

    public function getVehicleGroupsArray(): ?array
    {
        return $this->getVehicle()?->getGroupsArray();
    }
}
