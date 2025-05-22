<?php

namespace App\Entity;

use App\Entity\Xero\XeroClientAccount;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Client
 */
#[ORM\Table(name: 'client')]
#[ORM\Index(name: 'client_tax_nr_index', columns: ['tax_nr'])]
#[ORM\Index(name: 'client_chevron_account_id_index', columns: ['chevron_account_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\ClientRepository')]
#[ORM\EntityListeners(['App\EventListener\Client\ClientEntityListener'])]
#[ORM\HasLifecycleCallbacks]
class Client extends BaseEntity
{
    use AttributesTrait;

    public const ALLOWED_STATUSES = [
        self::STATUS_CLIENT,
        self::STATUS_POTENTIAL,
        self::STATUS_BLOCKED,
        self::STATUS_DEMO,
        self::STATUS_DELETED,
        self::STATUS_CLOSED,
        self::STATUS_PARTIALLY_BLOCKED_BILLING,
        self::STATUS_BLOCKED_BILLING,
        self::STATUS_BLOCKED_OVERDUE,
    ];

    public const LIST_STATUSES = [
        self::STATUS_CLIENT,
        self::STATUS_POTENTIAL,
        self::STATUS_BLOCKED,
        self::STATUS_DEMO,
        self::STATUS_CLOSED,
        self::STATUS_PARTIALLY_BLOCKED_BILLING,
        self::STATUS_BLOCKED_BILLING,
        self::STATUS_BLOCKED_OVERDUE,
    ];

    public const STATUS_BLOCKED_NTF = [
        self::STATUS_BLOCKED,
        self::STATUS_CLOSED,
        self::STATUS_BLOCKED_BILLING
    ];

    public const STATUS_CLIENT = 'client';
    public const STATUS_POTENTIAL = 'potential';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DEMO = 'demo';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_PARTIALLY_BLOCKED_BILLING = 'partially_blocked_billing';
    public const STATUS_BLOCKED_BILLING = 'blocked_billing';
    public const STATUS_BLOCKED_OVERDUE = 'blocked_overdue';

    public const INVOICE_DUE_DAYS = 7;

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'managerId',
        'salesManagerId',
        'team',
        'keyContactId',
        'timezone',
        'accountingContact',
        'planId',
        'status',
        'legalName',
        'legalAddress',
        'billingAddress',
        'taxNr',
        'chevronAccountId',
        'createdAt',
        'createdById',
        'createdByName',
        'updatedAt',
        'usersCount',
        'activeUsersCount',
        'devicesCount',
        'activeDevicesCount',
        'vehiclesCount',
        'activeVehiclesCount',
        'expirationDate',
        'language',
        'contractMonths',
        'billingPlanId',
        'country',
        'isManualPayment',
        'oldestInvoiceId',
        'invoiceDueDays',
        'allowManualPayment',
    ];

    public const SIMPLE_DISPLAY_VALUES = [
        'id',
        'name',
        'managerId',
        'salesManagerId',
        'team',
        'keyContactId',
        'timezone',
        'accountingContact',
        'planId',
        'status',
        'legalName',
        'legalAddress',
        'billingAddress',
        'taxNr',
        'chevronAccountId',
        'createdAt',
        'createdById',
        'createdByName',
        'updatedAt',
        'expirationDate',
        'loginWithId',
        'language',
        'billingPlanId',
        'contractMonths',
        'country',
        'isManualPayment',
        'oldestInvoiceId',
        'invoiceDueDays',
        'allowManualPayment',
    ];

    public const DEFAULT_GPS_STATUS_DURATION = 4 * 60 * 60;

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('managerId', $include, true)) {
            $data['managerId'] = $this->getManager()?->getId();
        }

        if (in_array('salesManagerId', $include, true)) {
            $data['salesManagerId'] = $this->getSalesManager()?->getId();
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }

        if (in_array('teamWithIsChevron', $include, true)) {
            $data['team'] = $this->getTeam()->toArray(array_merge(Team::DEFAULT_DISPLAY_VALUES, ['options']));
        }

        if (in_array('keyContactId', $include, true)) {
            $data['keyContactId'] = $this->getKeyContact()?->getId();
        }

        if (in_array('timezone', $include, true)) {
            $data['timezone'] = $this->getTimeZone()?->getId();
        }

        if (in_array('accountingContact', $include, true)) {
            $data['accountingContact'] = $this->getAccountingContact()?->getId();
        }

        if (in_array('planId', $include, true)) {
            $data['planId'] = $this->getPlan()?->getId();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->status;
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

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }

        if (in_array('createdById', $include, true)) {
            $data['createdById'] = $this->getCreatedBy()?->getId();
        }

        if (in_array('createdByName', $include, true)) {
            $data['createdByName'] = $this->getCreatedBy()?->getFullName();
        }

        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->updatedAt);
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

        if (in_array('vehiclesCount', $include, true)) {
            $data['vehiclesCount'] = $this->getVehiclesCount();
        }

        if (in_array('activeVehiclesCount', $include, true)) {
            $data['activeVehiclesCount'] = $this->getActiveVehiclesCount();
        }

        if (in_array('manager', $include, true)) {
            $data['manager'] = $this->getManager()?->toArray(User::CLIENT_LIST_DISPLAYED_VALUES);
        }

        if (in_array('salesManager', $include, true)) {
            $data['salesManager'] = $this->getSalesManager()?->toArray(User::CLIENT_LIST_DISPLAYED_VALUES);
        }

        if (in_array('keyContact', $include, true)) {
            $data['keyContact'] = $this->getKeyContact()?->toArray(User::CLIENT_LIST_DISPLAYED_VALUES);
        }

        if (in_array('plan', $include, true)) {
            $data['plan'] = $this->getPlan()?->toArray([], $this->team);
        }

        if (in_array('expirationDate', $include, true)) {
            $data['expirationDate'] = $this->formatDate($this->expirationDate);
        }

        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }

        if (in_array('language', $include, true)) {
            $data['language'] = $this->getLanguage();
        }

        if (in_array('contractMonths', $include, true)) {
            $data['contractMonths'] = $this->getContractMonths();
        }

        if (in_array('billingPlanId', $include, true)) {
            $data['billingPlanId'] = $this->getBillingPlan()?->getId();
        }

        if (in_array('country', $include, true)) {
            $data['country'] = $this->getCountry();
        }

        if (in_array('isManualPayment', $include, true)) {
            $data['isManualPayment'] = $this->isManualPayment();
        }

        if (in_array('oldestInvoiceId', $include, true)) {
            $data['oldestInvoiceId'] = $this->getOldestInvoiceId();
        }

        if (in_array('invoiceDueDays', $include, true)) {
            $data['invoiceDueDays'] = $this->getInvoiceDueDays();
        }

        if (in_array('waed', $include, true)) {
            $data['waed'] = $this->getWaed()?->format('c');
        }

        if (in_array('chevronAccountId', $include, true)) {
            $data['chevronAccountId'] = $this->getChevronAccountId();
        }

        if (in_array('allowManualPayment', $include, true)) {
            $data['allowManualPayment'] = $this->isAllowManualPayment();
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
            $data['status'] = ucfirst($this->getStatus());
        }

        if (in_array('plan', $include, true)) {
            $data['plan'] = $this->getPlan()?->getDisplayName();
        }

        if (in_array('contractMonths', $include, true)) {
            $data['contractMonths'] = $this->getContractMonths();
        }

        if (in_array('keyContactName', $include, true)) {
            $data['keyContact'] = $this->getKeyContact()?->getFullName();
        }

        if (in_array('salesManager', $include, true)) {
            $data['salesManager'] = $this->getSalesManager()?->getFullName();
        }

        if (in_array('manager', $include, true)) {
            $data['manager'] = $this->getManager()?->getFullName();
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

        if (in_array('vehiclesCount', $include, true)) {
            $data['vehiclesCount'] = $this->getVehiclesCount();
        }

        if (in_array('activeVehiclesCount', $include, true)) {
            $data['activeVehiclesCount'] = $this->getActiveVehiclesCount();
        }

        if (in_array('waed', $include, true)) {
            $data['waed'] = $this->getWaed()?->format(self::EXPORT_DATE_FORMAT);
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->getCreatedAtFormatted();
        }

        return $data;
    }

    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->team = $fields['team'] ?? null;
        $this->legalName = $fields['legalName'] ?? null;
        $this->taxNr = $fields['taxNr'] ?? null;
        $this->legalAddress = $fields['legalAddress'] ?? null;
        $this->billingAddress = $fields['billingAddress'] ?? null;
        $this->status = strtolower($fields['status']) ?? null;
        $this->plan = $fields['plan'] ?? null;
        $this->manager = $fields['manager'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->expirationDate = $fields['expirationDate'] ?? null;
        $this->loginWithId = $fields['loginWithId'] ?? false;
        $this->contractMonths = $fields['contractMonths'] ?? 36;
        $this->ownerTeam = $fields['ownerTeam'] ?? null;
        $this->country = $fields['country'] ?? null;
        $this->invoiceDueDays = $fields['invoiceDueDays'] ?? self::INVOICE_DUE_DAYS;
        $this->invoices = new ArrayCollection();
        $this->chevronAccountId = $fields['chevronAccountId'] ?? null;
        $this->allowManualPayment = $fields['allowManualPayment'] ?? false;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', nullable: true)]
    private $manager;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'sales_manager_id', referencedColumnName: 'id', nullable: true)]
    private $salesManager;

    /**
     * @var User
     */
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'key_contact_id', referencedColumnName: 'id')]
    private $keyContact;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'client')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var User
     */
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'accounting_contact_id', referencedColumnName: 'id', nullable: true)]
    private $accountingContact;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Plan')]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id')]
    private $plan;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: true)]
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
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'expiration_date', type: 'datetime', nullable: true)]
    private $expirationDate;

    private $timeZone;

    private $language;

    /**
     * @var int
     */
    #[ORM\Column(name: 'contract_months', type: 'integer', nullable: true)]
    private $contractMonths;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'ownerTeam')]
    #[ORM\JoinColumn(name: 'owner_team_id', referencedColumnName: 'id')]
    private $ownerTeam;

    #[ORM\OneToOne(mappedBy: 'client', targetEntity: 'App\Entity\Xero\XeroClientAccount', fetch: 'EXTRA_LAZY')]
    private $xeroClientAccount;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $stripeId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'country', type: 'string', length: 50, nullable: true)]
    private $country;

    #[ORM\Column(type: 'boolean', options: ['default' => 'true'])]
    private $isManualPayment = true;

    /**
     * @var ArrayCollection|Invoice[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Invoice', mappedBy: 'client', fetch: 'EXTRA_LAZY')]
    private $invoices;

    /**
     * @var int
     */
    #[ORM\Column(name: 'invoice_due_days', type: 'integer', nullable: true)]
    private $invoiceDueDays;

    /**
     * @Assert\Length(
     *      max = 255
     * )
     */
    #[ORM\Column(name: 'chevron_account_id', type: 'string', length: 255, nullable: true)]
    private ?string $chevronAccountId;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'allow_manual_payment', type: 'boolean', nullable: false, options: ['default' => 0])]
    private bool $allowManualPayment = false;

    private EntityManager $em;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set manager
     *
     * @param User $manager
     *
     * @return Client
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
     * @param User|null $salesManager
     * @return $this
     */
    public function setSalesManager(?User $salesManager): self
    {
        $this->salesManager = $salesManager;

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
     * Set team
     *
     * @param Team $team
     *
     * @return Client
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
     * Set keyContact
     *
     * @param User $keyContact
     *
     * @return Client
     */
    public function setKeyContact($keyContact)
    {
        $this->keyContact = $keyContact;

        return $this;
    }

    /**
     * Get keyContact
     *
     * @return User
     */
    public function getKeyContact()
    {
        return $this->keyContact;
    }

    /**
     * Set plan
     *
     * @param Plan $plan
     *
     * @return Client
     */
    public function setPlan(?Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get planId
     *
     * @return Plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Client
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set legalName
     *
     * @param string $legalName
     *
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Client
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
     * @return Client
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
     * @return string|null
     */
    public function getCreatedByName(): ?string
    {
        return $this->createdBy?->getFullName();
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Client
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
     * @return Client
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
        return $this->getUpdatedBy()?->toArray(User::CREATED_BY_FIELDS);
    }

    /**
     * @return string|null
     */
    public function getUpdatedByName(): ?string
    {
        return $this->getUpdatedBy()?->getFullName();
    }

    /**
     * Get updatedBy
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->getTeam()->getUsers();
    }

    /**
     * @return int
     */
    public function getUsersCount()
    {
        return $this->getTeam()->getUsers()->count();
    }

    /**
     * @return int
     */
    public function getActiveUsersCount()
    {
        return $this->getTeam()->getUsers()->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', User::STATUS_ACTIVE))
        )->count();
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

    /**
     * Get Vehicles Count
     *
     * @return int
     */
    public function getVehiclesCount()
    {
        return $this->getTeam()->getVehicles()->count();
    }

    /**
     * Get Active Vehicles Count
     *
     * @return int
     */
    public function getActiveVehiclesCount()
    {
        return $this->getTeam()->getVehicles()->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->in('status', Vehicle::ACTIVE_STATUSES_LIST))
        )->count();
    }

    /**
     * @return User|null
     */
    public function getAccountingContact(): ?User
    {
        return $this->accountingContact;
    }

    /**
     * @param User $accountingContact
     */
    public function setAccountingContact(User $accountingContact): void
    {
        $this->accountingContact = $accountingContact;
    }

    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return \DateTime|null
     */
    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTime $expirationDate
     */
    public function setExpirationDate(?\DateTime $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @param TimeZone $timeZone
     */
    public function setTimeZone(TimeZone $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * @return TimeZone
     */
    public function getTimeZone(): ?TimeZone
    {
        return $this->timeZone;
    }

    /**
     * @return mixed
     */
    public function getTimeZoneSetting()
    {
        return $this->getTeam()->getSettingsByName(Setting::TIMEZONE_SETTING);
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
    public function getTimeZoneName()
    {
        return $this->getTimeZone() ? $this->getTimeZone()->getName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    public function getReseller(): ?Reseller
    {
        if ($this->getOwnerTeam()?->isResellerTeam()) {
            return $this->getOwnerTeam()->getReseller();
        }

        return null;
    }

    public function isClosed()
    {
        return $this->getStatus() === self::STATUS_CLOSED;
    }

    public function isBlocked()
    {
        return $this->getStatus() === self::STATUS_BLOCKED;
    }

    public function isBlockedBilling(): bool
    {
        return $this->getStatus() === self::STATUS_BLOCKED_BILLING;
    }

    public function isDeleted()
    {
        return $this->getStatus() === self::STATUS_DELETED;
    }

    public function isInDemo()
    {
        return $this->getStatus() === self::STATUS_DEMO;
    }
    
    public function isBlockedOverdue()
    {
        return $this->getStatus() === self::STATUS_BLOCKED_OVERDUE;
    }

    public function setContractMonths(?int $contactMonths): self
    {
        $this->contractMonths = $contactMonths;

        return $this;
    }

    public function getContractMonths(): ?int
    {
        return $this->contractMonths;
    }

    public function getBillingPlan()
    {
        return $this->getTeam()->getBillingPlan();
    }

    public function getNameSort(): string
    {
        return $this->name;
    }

    public function getOwnerTeam(): ?Team
    {
        return $this->ownerTeam;
    }

    public function setOwnerTeam(Team $team): self
    {
        $this->ownerTeam = $team;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return XeroClientAccount
     */
    public function getXeroClientAccount()
    {
        return $this->xeroClientAccount;
    }

    /**
     * @param XeroClientAccount $xeroClientAccount
     */
    public function setXeroClientAccount(XeroClientAccount $xeroClientAccount): void
    {
        $this->xeroClientAccount = $xeroClientAccount;
    }

    /**
     * @return mixed
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * @param string $stripeId
     */
    public function setStripeId($stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasStripeId()
    {
        return !is_null($this->stripeId);
    }

    public function isManualPayment(): ?bool
    {
        return $this->isManualPayment;
    }

    public function setIsManualPayment(bool $isManualPayment): self
    {
        $this->isManualPayment = $isManualPayment;

        return $this;
    }

    public function getInvoices()
    {
        return $this->invoices;
    }

    public function getOldestInvoiceId()
    {
        $invoice = $this->getInvoices()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('type', Invoice::TYPE_REGULAR))
                ->andWhere(Criteria::expr()->eq('status', Invoice::STATUS_NOT_PAID))
                ->orderBy(['createdAt' => Criteria::ASC])
        )->first();

        return $invoice ? $invoice->getId() : null;
    }

    public function getOldestOverdueInvoice(): ?Invoice
    {
        return $this->getInvoices()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('type', Invoice::TYPE_REGULAR))
                ->andWhere(Criteria::expr()->eq('status', Invoice::STATUS_NOT_PAID))
                ->andWhere(Criteria::expr()->lt('dueAt', new \DateTime()))
                ->orderBy(['dueAt' => Criteria::ASC])
        )->first() ?: null;
    }

    public function getOverdueInvoices()
    {
        return $this->getInvoices()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('type', Invoice::TYPE_REGULAR))
                ->andWhere(Criteria::expr()->eq('status', Invoice::STATUS_NOT_PAID))
                ->andWhere(Criteria::expr()->lt('dueAt', new \DateTime()))
                ->orderBy(['dueAt' => Criteria::ASC])
        );
    }

    public function getInvoiceDueDays(): ?int
    {
        return $this->invoiceDueDays;
    }

    public function setInvoiceDueDays(int $days): self
    {
        $this->invoiceDueDays = $days;

        return $this;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    public function getWaed(): ?\DateTime
    {
        return $this->em->getRepository(Device::class)->getWaedByTeam($this->getTeam());
    }

    public function isChevron(): bool
    {
        return $this->getTeam()->isChevron();
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

    public function getSignPostSettingValue(): bool
    {
        $addons = $this->getTeam()->getSettingsByName(Setting::BILLABLE_ADDONS);

        return in_array(Setting::BILLABLE_ADDONS_SIGN_POST_SPEED_DATA, $addons->getValue());
    }

    /**
     * @return bool
     */
    public function isAllowManualPayment(): bool
    {
        return $this->allowManualPayment;
    }

    /**
     * @param bool $allowManualPayment
     */
    public function setAllowManualPayment(bool $allowManualPayment): void
    {
        $this->allowManualPayment = $allowManualPayment;
    }
}

