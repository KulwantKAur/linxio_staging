<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reseller
 */
#[ORM\Table(name: 'reseller')]
#[ORM\Index(name: 'reseller_tax_nr_index', columns: ['tax_nr'])]
#[ORM\Index(name: 'reseller_chevron_account_id_index', columns: ['chevron_account_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\ResellerRepository')]
#[ORM\EntityListeners(['App\EventListener\Reseller\ResellerEntityListener'])]
class Reseller extends BaseEntity
{
    use AttributesTrait;

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_BLOCKED,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_BLOCKED
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'companyName',
        'keyContactId',
        'legalName',
        'legalAddress',
        'billingAddress',
        'taxNr',
        'chevronAccountId',
        'manager',
        'salesManager',
        'timezone',
        'theme',
        'productName',
        'team',
        'status',
        'createdAt',
        'updatedAt',
        'language'
    ];

    public const SIMPLE_VALUES = [
        'companyName',
        'keyContactId',
        'legalName',
        'productName',
        'team',
        'status',
        'language'
    ];

    public const EDITABLE_FIELDS = [
        'companyName',
        'keyContact',
        'legalName',
        'legalAddress',
        'billingAddress',
        'taxNr',
        'chevronAccountId',
        'manager',
        'salesManager',
        'timezone',
        'phone',
        'theme',
        'status',
        'updatedAt',
        'updatedBy',
        'status'
    ];

    public function __construct(array $fields)
    {
        $this->companyName = $fields['companyName'] ?? null;
        $this->keyContact = $fields['keyContact'] ?? null;
        $this->legalName = $fields['legalName'] ?? null;
        $this->legalAddress = $fields['legalAddress'] ?? null;
        $this->billingAddress = $fields['billingAddress'] ?? null;
        $this->taxNr = $fields['taxNr'] ?? null;
        $this->manager = $fields['manager'] ?? null;
        $this->salesManager = $fields['salesManager'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->theme = $fields['theme'] ?? null;
        $this->chevronAccountId = $fields['chevronAccountId'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('companyName', $include, true)) {
            $data['companyName'] = $this->getCompanyName();
        }
        if (in_array('keyContact', $include, true)) {
            $data['keyContact'] = $this->getKeyContact() ? $this->getKeyContact()->toArray(User::SIMPLE_VALUES) : null;
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('timezone', $include, true)) {
            $data['timezone'] = $this->getTimezoneData();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('teamWithIsChevron', $include, true)) {
            $data['team'] = $this->getTeam()->toArray(array_merge(Team::DEFAULT_DISPLAY_VALUES, ['options']));
        }
        if (in_array('legalName', $include, true)) {
            $data['legalName'] = $this->legalName;
        }
        if (in_array('legalAddress', $include, true)) {
            $data['legalAddress'] = $this->legalAddress;
        }
        if (in_array('billingAddress', $include, true)) {
            $data['billingAddress'] = $this->billingAddress;
        }
        if (in_array('taxNr', $include, true)) {
            $data['taxNr'] = $this->getTaxNr();
        }
        if (in_array('manager', $include, true)) {
            $data['manager'] = $this->manager ? $this->getManager()->toArray(User::DISPLAYED_VALUES) : null;
        }
        if (in_array('managerId', $include, true)) {
            $data['managerId'] = $this->manager ? (int)$this->getManager()->getId() : null;
        }
        if (in_array('salesManager', $include, true)) {
            $data['salesManager'] = $this->salesManager ? $this->getSalesManager()->toArray(User::DISPLAYED_VALUES) : null;
        }
        if (in_array('salesManagerId', $include, true)) {
            $data['salesManagerId'] = $this->salesManager ? (int)$this->getSalesManager()->getId() : null;
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->updatedAt);
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }
        if (in_array('createdByName', $include, true)) {
            $data['createdByName'] = $this->getCreatedByName();
        }
        if (in_array('usersCount', $include, true)) {
            $data['usersCount'] = $this->getUsersCount();
        }
        if (in_array('activeUsersCount', $include, true)) {
            $data['activeUsersCount'] = $this->getActiveUsersCount();
        }
        if (in_array('devicesCount', $include, true)) {
            $data['devicesCount'] = $this->getDevicesCount();
        }
        if (in_array('activeDevicesCount', $include, true)) {
            $data['activeDevicesCount'] = $this->getActiveDevicesCount();
        }
        if (in_array('theme', $include, true)) {
            $data['theme'] = $this->getTheme() ? $this->getTheme()->toArray() : null;
        }
        if (in_array('language', $include, true)) {
            $data['language'] = $this->getLanguage();
        }
        if (in_array('chevronAccountId', $include, true)) {
            $data['chevronAccountId'] = $this->getChevronAccountId();
        }

        return $data;
    }

    public function toExport(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('companyName', $include, true)) {
            $data['companyName'] = $this->getCompanyName();
        }
        if (in_array('keyContact', $include, true)) {
            $data['keyContact'] = $this->getKeyContact() ? $this->getKeyContact()->getFullName() : null;
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('legalName', $include, true)) {
            $data['legalName'] = $this->legalName;
        }
        if (in_array('legalAddress', $include, true)) {
            $data['legalAddress'] = $this->legalAddress;
        }
        if (in_array('billingAddress', $include, true)) {
            $data['billingAddress'] = $this->billingAddress;
        }
        if (in_array('taxNr', $include, true)) {
            $data['taxNr'] = $this->taxNr;
        }
        if (in_array('manager', $include, true)) {
            $data['manager'] = $this->manager ? $this->getManager()->getFullName() : null;
        }
        if (in_array('usersCount', $include, true)) {
            $data['usersCount'] = $this->getUsersCount();
        }
        if (in_array('activeUsersCount', $include, true)) {
            $data['activeUsersCount'] = $this->getActiveUsersCount();
        }
        if (in_array('devicesCount', $include, true)) {
            $data['devicesCount'] = $this->getDevicesCount();
        }
        if (in_array('activeDevicesCount', $include, true)) {
            $data['activeDevicesCount'] = $this->getActiveDevicesCount();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate(
                $this->createdAt,
                self::EXPORT_DATE_FORMAT,
                $this->getCreatedBy()->getTimezone()
            );
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
     * @var string|null
     * @Assert\Length(max=255)
     */
    #[ORM\Column(name: 'company_name', type: 'string', length: 255, nullable: true)]
    private $companyName;

    /**
     * @var User
     */
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'key_contact_id', referencedColumnName: 'id', nullable: true)]
    private $keyContact;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: false)]
    private $status;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'legal_name', type: 'string', length: 255, nullable: true)]
    private $legalName;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'legal_address', type: 'string', length: 255, nullable: true)]
    private $legalAddress;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'billing_address', type: 'string', length: 255, nullable: true)]
    private $billingAddress;

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = 255
     * )
     */
    #[ORM\Column(name: 'tax_nr', type: 'string', length: 255, nullable: true)]
    private $taxNr;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id')]
    private $manager;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'sales_manager_id', referencedColumnName: 'id')]
    private $salesManager;

    /**
     * @var string|null
     * @Assert\Length(max=100)
     */
    #[ORM\Column(name: 'email', type: 'string', length: 100, nullable: true)]
    private $email;

    /**
     * @var Team
     */
    #[ORM\OneToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

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

    private $timezone;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'Theme')]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id')]
    private $theme;

    private $language;

    /**
     * @var int
     */
    #[ORM\OneToMany(targetEntity: 'Note', mappedBy: 'reseller')]
    private $notes;

    /**
     * @Assert\Length(
     *      max = 255
     * )
     */
    #[ORM\Column(name: 'chevron_account_id', type: 'string', length: 255, nullable: true)]
    private ?string $chevronAccountId;

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
     * Set companyName.
     *
     * @param string|null $companyName
     *
     * @return Reseller
     */
    public function setCompanyName($companyName = null)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set legalName
     *
     * @param string $legalName
     *
     * @return Reseller
     */
    public function setLegalName($legalName)
    {
        $this->legalName = $legalName;

        return $this;
    }

    /**
     * Get legalName
     *
     * @return string
     */
    public function getLegalName()
    {
        return $this->legalName;
    }

    /**
     * Set legalAddress
     *
     * @param string $legalAddress
     *
     * @return Reseller
     */
    public function setLegalAddress($legalAddress)
    {
        $this->legalAddress = $legalAddress;

        return $this;
    }

    /**
     * Get legalAddress
     *
     * @return string
     */
    public function getLegalAddress()
    {
        return $this->legalAddress;
    }

    /**
     * Set billingAddress
     *
     * @param string $billingAddress
     *
     * @return Reseller
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Get billingAddress
     *
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set taxNr
     *
     * @param string $taxNr
     *
     * @return Reseller
     */
    public function setTaxNr($taxNr)
    {
        $this->taxNr = $taxNr;

        return $this;
    }

    /**
     * Get taxNr
     *
     * @return string
     */
    public function getTaxNr()
    {
        return $this->taxNr;
    }

    /**
     * Set manager
     *
     * @param User $manager
     *
     * @return Reseller
     */
    public function setManager(?User $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return User
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param User|null $manager
     * @return $this
     */
    public function setSalesManager(?User $manager)
    {
        $this->salesManager = $manager;

        return $this;
    }

    /**
     * @return User|mixed|null
     */
    public function getSalesManager()
    {
        return $this->salesManager;
    }

    /**
     * Set timezone.
     *
     * @param TimeZone|null $timezone
     *
     * @return Reseller
     */
    public function setTimezone($timezone = null)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone.
     *
     * @return TimeZone|null
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getTimezoneData(): ?array
    {
        return $this->timezone ? $this->timezone->toArray() : null;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param User|null $keyContact
     * @return $this
     */
    public function setKeyContact(?User $keyContact)
    {
        $this->keyContact = $keyContact;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getKeyContact(): ?User
    {
        return $this->keyContact;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return Reseller
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
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Get teamId
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()->getId();
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Reseller
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAtFormatted
     *
     * @return string
     */
    public function getCreatedAtFormatted()
    {
        return $this->createdAt ? Carbon::createFromTimestamp($this->createdAt->getTimestamp())->format(
            self::EXPORT_DATE_FORMAT
        ) : null;
    }

    /**
     * @return Carbon|\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Reseller
     */
    public function setCreatedBy($createdBy)
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
     * @return string
     */
    public function getCreatedByName(): ?string
    {
        return $this->createdBy ? $this->createdBy->getFullName() : null;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Reseller
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return Reseller
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTimeZoneSetting()
    {
        return $this->getTeam()->getSettingsByName(Setting::TIMEZONE_SETTING);
    }

    public function checkAsResellerTeamAccess(Team $team)
    {
        if ($team->isClientTeam() && $team->getClient()->getOwnerTeam()?->getId() === $this->getTeamId()) {
            return true;
        } elseif ($team->isResellerTeam() && $team->getId() === $this->getTeamId()) {
            return true;
        } else {
            return false;
        }
    }

    public function getActiveUsersCount()
    {
        return $this->getTeam()->getUsers()->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', User::STATUS_ACTIVE))
        )->count();
    }

    public function getUsersCount()
    {
        return $this->getTeam()->getUsers()->count();
    }

    /**
     * Get Devices Count
     *
     * @return int
     */
    public function getDevicesCount()
    {
        return $this->getTeam()->getDevices()->count();
    }

    /**
     * Get Active Devices Count
     *
     * @return int
     */
    public function getActiveDevicesCount()
    {
        return $this->getTeam()->getDevices()->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('isDeactivated', false))
        )->count();
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme($theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguageSetting()
    {
        return $this->getTeam()->getSettingsByName(Setting::LANGUAGE_SETTING);
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string|null
     */
    public function getBusinessId(): ?string
    {
        return $this->getTaxNr();
    }

    /**
     * @return string|null
     */
    public function getChevronAccountId(): ?string
    {
        return $this->chevronAccountId;
    }

    /**
     * @param string|null $chevronAccountId
     */
    public function setChevronAccountId(?string $chevronAccountId): void
    {
        $this->chevronAccountId = $chevronAccountId;
    }

    public function isChevron(): bool
    {
        return $this->getTeam()->isChevron();
    }
}
