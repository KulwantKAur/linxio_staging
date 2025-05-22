<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use App\Util\AttributesTrait;
use App\Util\DateHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DocumentRecord
 */
#[ORM\Table(name: 'document_record')]
#[ORM\Entity(repositoryClass: 'App\Repository\DocumentRecordRepository')]
#[ORM\EntityListeners(['App\EventListener\Document\DocumentRecordEntityListener'])]
#[ORM\HasLifecycleCallbacks]
class DocumentRecord extends BaseEntity
{
    use AttributesTrait;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRE_SOON = 'expire_soon';
    public const STATUS_EXPIRED = 'expired';

    public const ALLOWED_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRE_SOON,
        self::STATUS_EXPIRED,
    ];

    public const DASHBOARD_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRE_SOON,
        self::STATUS_EXPIRED,
    ];

    public const EDITABLE_FIELDS = [
        'issueDate',
        'expDate',
        'cost',
        'note',
        'updatedAt',
        'updatedBy',
        'createdBy',
        'noExpiry'
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'issueDate',
        'expDate',
        'cost',
        'note',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'files',
        'noExpiry'
    ];

    public const DATA_FIELDS = [
        'vehicle',
        'issueDate',
        'expDate',
        'cost',
        'note',
        'remainingDays',
        'noExpiry'
    ];

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

        if (in_array('issueDate', $include, true)) {
            $data['issueDate'] = $this->formatDate($this->issueDate);
        }

        if (in_array('expDate', $include, true)) {
            $data['expDate'] = $this->formatDate($this->expDate);
        }

        if (in_array('cost', $include, true)) {
            $data['cost'] = $this->cost;
        }

        if (in_array('note', $include, true)) {
            $data['note'] = $this->note;
        }

        if (in_array('remainingDays', $include, true)) {
            $data['remainingDays'] = $this->getRemainingDays();
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

        if (in_array('files', $include, true)) {
            $data['files'] = $this->getFilesArray();
        }

        if (in_array('fileIds', $include, true)) {
            $data['fileIds'] = $this->getFilesArrayIds();
        }

        if (in_array('document', $include, true)) {
            $data['document'] = $this->getDocument()->toArray();
        }

        if (in_array('noExpiry', $include, true)) {
            $data['noExpiry'] = $this->getNoExpiry();
        }

        return $data;
    }

    /**
     * Document constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->issueDate = $fields['issueDate'] ?? null;
        $this->expDate = $fields['expDate'] ?? null;
        $this->cost = $fields['cost'] ?? null;
        $this->note = $fields['note'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->files = new ArrayCollection();
        $this->noExpiry = $fields['noExpiry'] ?? false;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Document
     */
    #[ORM\ManyToOne(targetEntity: 'Document', inversedBy: 'records')]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id')]
    private $document;

    /**
     * @var ArrayCollection
     */
    #[ORM\JoinTable(name: 'document_record_file')]
    #[ORM\JoinColumn(name: 'document_record_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'file_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'File')]
    private $files;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status = self::STATUS_ACTIVE;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'issue_date', type: 'datetime', nullable: true)]
    private $issueDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'exp_date', type: 'datetime', nullable: true)]
    private $expDate;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'cost', type: 'float', nullable: true)]
    private $cost;

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
     * @var bool
     */
    #[ORM\Column(name: 'no_expiry', type: 'boolean', options: ['default' => 'false'])]
    private bool $noExpiry = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @param Document $document
     * @return DocumentRecord
     */
    public function setDocument(Document $document): DocumentRecord
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getFilesArray(array $fields = []): array
    {
        return array_values(
            array_map(
                static function (File $file) use ($fields) {
                    $file->setPath(LocalFileService::VEHICLE_DOCUMENT_PUBLIC_PATH);
                    return $file->toArray($fields);
                },
                $this->files->toArray()
            )
        );
    }

    /**
     * @return array
     */
    public function getFilesArrayIds(): array
    {
        return array_values(
            array_map(
                static function (File $file) {
                    return $file->getId();
                },
                $this->files->toArray()
            )
        );
    }

    /**
     * @param $files
     * @return DocumentRecord
     */
    public function setFiles($files): DocumentRecord
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @param File $file
     * @return DocumentRecord
     */
    public function addFile(File $file): DocumentRecord
    {
        $this->files->add($file);

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function removeFiles(array $ids)
    {
        $this->files = array_filter(
            $this->files->toArray(),
            static function (File $file) use ($ids) {
                return !in_array($file->getId(), $ids);
            }
        );
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
     * @return DocumentRecord
     */
    public function setStatus(string $status): DocumentRecord
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getIssueDate(): ?\DateTime
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime|null $issueDate
     * @return DocumentRecord
     */
    public function setIssueDate(?\DateTime $issueDate): DocumentRecord
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpDate(): ?\DateTime
    {
        return $this->expDate;
    }

    /**
     * @param \DateTime|null $expDate
     * @return DocumentRecord
     */
    public function setExpDate(?\DateTime $expDate): DocumentRecord
    {
        $this->expDate = $expDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getNotifyDate(): ?\DateTime
    {
        if ($this->expDate) {
            $notifyDate = clone $this->expDate;
            if (0 === $this->getDocument()->getNotifyBefore()) {
                return $notifyDate;
            }

            return $notifyDate->modify(sprintf('-%d days', $this->getDocument()->getNotifyBefore()));
        }

        return null;
    }

    /**
     * @param int|null $notifyBefore
     * @return DocumentRecord
     */
    public function setNotifyBefore(?int $notifyBefore): DocumentRecord
    {
        $this->notifyBefore = $notifyBefore;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCost(): ?float
    {
        return $this->cost;
    }

    /**
     * @param float|null $cost
     * @return DocumentRecord
     */
    public function setCost(?float $cost): DocumentRecord
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     * @return DocumentRecord
     */
    public function setNote(?string $note): DocumentRecord
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getRemainingDays(): ?int
    {
        if ($this->expDate) {
            return DateHelper::getDaysCountBeforeDate($this->expDate);
        }

        return null;
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
     * @return DocumentRecord
     */
    public function setCreatedAt(\DateTime $createdAt): DocumentRecord
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
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
     */
    public function getCreatedByArray(): ?array
    {
        return $this->createdBy ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param User|null $createdBy
     * @return DocumentRecord
     */
    public function setCreatedBy(?User $createdBy): DocumentRecord
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return DocumentRecord
     */
    public function setUpdatedAt(?\DateTime $updatedAt): DocumentRecord
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**]
     * @return User|null
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
     */
    public function getUpdatedByArray(): ?array
    {
        return $this->updatedBy ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param User $updatedBy
     * @return DocumentRecord
     */
    public function setUpdatedBy(?User $updatedBy): DocumentRecord
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->getDocument()->getTeam();
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getDocument()->getTimeZoneName();
    }

    public function getDriver()
    {
        return $this->getDocument()->getDriver();
    }

    public function getVehicle()
    {
        return $this->getDocument()->getVehicle();
    }

    public function getAsset()
    {
        return $this->getDocument()->getAsset();
    }

    public function setNoExpiry(bool $noExpiry): self
    {
        $this->noExpiry = $noExpiry;

        return $this;
    }

    public function getNoExpiry(): bool
    {
        return $this->noExpiry;
    }
}

