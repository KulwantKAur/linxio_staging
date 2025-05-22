<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use App\Util\AttributesTrait;
use App\Util\CountryHelper;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Table(name: 'invoice')]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Invoice extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'status',
        'clientId',
        'periodStart',
        'periodEnd',
        'amount',
        'tax',
        'totalAmount',
        'paymentFee',
        'createdAt',
        'updatedAt',
        'dueAt',
        'extInvoiceId',
        'totalAmountWithPrepayment',
        'hasPrepayment',
        'prepaymentAmount',
        'previousPrepaymentAmount',
        'totalTaxWithPrepayment',
        'amountWithPrepayment',
        'internalInvoiceId'
    ];

    public const STATUS_NOT_PAID = 'not_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_PROCESSING = 'payment_processing';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_PAYMENT_ERROR = 'payment_error';

    public const TYPE_REGULAR = 'regular';
    public const TYPE_PREPAYMENT = 'prepayment';
    public const TYPE_PREVIOUS_PREPAYMENT = 'previousPrepayment';

    public const PAYMENT_STATUS_ERROR = 'error';
    public const PAYMENT_STATUS_PROCESSING = 'processing';
    public const PAYMENT_STATUS_SUCCESS = 'success';

    public const GST_VALUE = 0.1;

    public const OVERDUE_AFTER_DAYS = 60;

    private $hasPrepayment = false;

    public const OVERDUE_PARTIALLY_BLOCKED_DAYS = 7;
    public const OVERDUE_BLOCKED_DAYS = 14;

    public const EXT_INVOICE_ID_ERROR = 'ext_invoice_id_error';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'bigint')]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'client_id', type: 'bigint')]
    private $clientId;

    /**
     * @var Client
     */
    #[ORM\ManyToOne(targetEntity: 'Client', fetch: 'EAGER', inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    private $client;


    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status = Invoice::STATUS_NOT_PAID;

    /**
     * @var string
     */
    #[ORM\Column(name: 'period_start', type: 'date')]
    private $periodStart;

    /**
     * @var string
     */
    #[ORM\Column(name: 'period_end', type: 'date')]
    private $periodEnd;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'due_at', type: 'datetime')]
    private $dueAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL')]
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

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: 'App\Entity\InvoiceDetails')]
    private $details;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $amount;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $paymentFee;

    /**
     * ID of invoice from Xero
     *
     * @var string
     */
    #[ORM\Column(name: 'ext_invoice_id', type: 'string', nullable: true)]
    private $extInvoiceId;

    /**
     * Payment ID from Stripe
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $paymentId;

    /**
     * Payment ID from Xero
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $extPaymentId;

    /**
     * Is this invoice paid in Xero?
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $extPaid = false;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $totalAmount = 0;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $tax = 0;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'regular'])]
    private $type = Invoice::TYPE_REGULAR;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $paymentStatus;

    #[ORM\OneToOne(targetEntity: Invoice::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'prepayment_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $prepayment;

    #[ORM\OneToOne(targetEntity: Invoice::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'previous_prepayment_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $previousPrepayment;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $stripeFee = 0;

    public function __construct(array $fields)
    {
        $this->periodStart = $fields['period_start'];
        $this->periodEnd = $fields['period_end'];
        $this->type = $fields['type'] ?? Invoice::TYPE_REGULAR;
        $this->client = $fields['client'];
        $this->dueAt = $fields['due_at'];
        $this->createdAt = Carbon::now('UTC');
        $this->amount = $fields['amount'] ?? 0;
        $this->tax = $fields['tax'] ?? 0;
        $this->paymentFee = $fields['payment_fee'] ?? 0;
        $this->totalAmount = $this->amount + $this->tax;
        $this->prepayment = $fields['prepayment'] ?? null;
        $this->previousPrepayment = $fields['previous_prepayment'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('periodStart', $include, true)) {
            $data['periodStart'] = $this->formatDate($this->getPeriodStart());
        }

        if (in_array('periodEnd', $include, true)) {
            $data['periodEnd'] = $this->formatDate($this->getPeriodEnd());
        }

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient()->toArray();
        }

        if (in_array('dueAt', $include, true)) {
            $data['dueAt'] = $this->formatDate($this->getDueAt());
        }

        if (in_array('amount', $include, true)) {
            $data['amount'] = $this->getAmount();
        }

        if (in_array('extInvoiceId', $include, true)) {
            $data['extInvoiceId'] = $this->getExtInvoiceId();
        }

        if (in_array('paymentFee', $include, true)) {
            $data['paymentFee'] = $this->getPaymentFee();
        }

        if (in_array('tax', $include, true)) {
            $data['tax'] = $this->getTax();
        }

        if (in_array('totalAmount', $include, true)) {
            $data['totalAmount'] = $this->getTotalAmount();
        }

        if (in_array('prepaymentAmount', $include, true)) {
            $data['prepaymentAmount'] = $this->getPrepayment()?->getAmount();
        }

        if (in_array('previousPrepaymentAmount', $include, true)) {
            $data['previousPrepaymentAmount'] = $this->getPreviousPrepayment()?->getAmount();
        }

        if (in_array('totalTaxWithPrepayment', $include, true)) {
            $data['totalTaxWithPrepayment'] = $this->getTotalTaxWithPrepayment();
        }

        if (in_array('hasPrepayment', $include, true)) {
            $data['hasPrepayment'] = (bool)$this->getPrepayment();
        }

        if (in_array('totalAmountWithPrepayment', $include, true)) {
            $data['totalAmountWithPrepayment'] = $this->getTotalWithPrepayment();
        }

        if (in_array('amountWithPrepayment', $include, true)) {
            $data['amountWithPrepayment'] = $this->getAmountWithPrepayment();
        }

        if (in_array('details', $include)) {
            $data['details'] = $this->getDetailsData();
        }
        if (in_array('internalInvoiceId', $include, true)) {
            $data['internalInvoiceId'] = $this->getInternalInvoiceId();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        return $data;
    }

    public function toExport(array $include = []): array
    {
        $data = [];

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('periodStart', $include, true)) {
            $data['periodStart'] = $this->formatDate($this->getPeriodStart(), self::EXPORT_DATE_FORMAT);
        }

        if (in_array('periodEnd', $include, true)) {
            $data['periodEnd'] = $this->formatDate($this->getPeriodEnd(), self::EXPORT_DATE_FORMAT);
        }

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient()->getName();
        }

        if (in_array('dueAt', $include, true)) {
            $data['dueAt'] = $this->formatDate($this->getDueAt(), self::EXPORT_DATE_FORMAT);
        }

        if (in_array('amount', $include, true)) {
            $data['amount'] = $this->getAmount();
        }

        if (in_array('extInvoiceId', $include, true)) {
            $data['extInvoiceId'] = $this->getExtInvoiceId();
        }
        if (in_array('internalInvoiceId', $include, true)) {
            $data['internalInvoiceId'] = $this->getInternalInvoiceId();
        }

        return $data;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
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
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodStart(): \DateTime
    {
        return $this->periodStart;
    }

    /**
     * @param string $period_start
     */
    public function setPeriodStart(mixed $period_start): void
    {
        $this->periodStart = $period_start;
    }

    /**
     * @return string
     */
    public function getPeriodEnd(): \DateTime
    {
        return $this->periodEnd;
    }

    /**
     * @param string $period_end
     */
    public function setPeriodEnd(mixed $period_end): void
    {
        $this->periodEnd = $period_end;
    }

    /**
     * @return \DateTime
     */
    public function getDueAt(): \DateTime
    {
        return $this->dueAt;
    }

    /**
     * @param \DateTime $dueAt
     */
    public function setDueAt(\DateTime $dueAt): void
    {
        $this->dueAt = $dueAt;
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
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return User
     */
    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     */
    public function setUpdatedBy(User $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function getDetailsData(): array
    {
        $data['details'] = [];

        if ($this->isFirstInvoice()) {
            foreach ($this->getPrepayment()->getDetails() as $invoiceDetails) {
                $data['details'][$invoiceDetails->getKey()] = $invoiceDetails->toArray();
            }

            return $data['details'];
        }
        if ($this->getDetails()) {
            foreach ($this->getDetails() as $invoiceDetails) {
                $data['details'][$invoiceDetails->getKey()] = $invoiceDetails->toArray();
            }
        } else {
            $data['details'] = [];
        }

        $data['details']['prepaymentData'] = [];
        if ($this->getPreviousPrepayment()) {
            $data['details']['prepaymentData'][self::TYPE_PREVIOUS_PREPAYMENT] = [
                'key' => self::TYPE_PREVIOUS_PREPAYMENT,
                'quantity' => 1,
                'price' => -round($this->getPreviousPrepayment()->getAmount(), 2),
                'total' => -round($this->getPreviousPrepayment()->getAmount(), 2),
            ];
        }

        if ($this->getPrepayment()) {
            $data['details']['prepaymentData'][self::TYPE_PREPAYMENT] = [
                'key' => self::TYPE_PREPAYMENT,
                'quantity' => 1,
                'price' => round($this->getPrepayment()->getAmount(), 2),
                'total' => round($this->getPrepayment()->getAmount(), 2),
            ];
        }

        return $data['details'];
    }

    /**
     * @param InvoiceDetails $details
     */
    public function setDetails(InvoiceDetails $details): void
    {
        $this->details = $details;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return round($this->amount, 2);
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
        $this->totalAmount = $this->amount + $this->tax;
    }

    /**
     * @return string
     */
    public function getExtInvoiceId(): ?string
    {
        return $this->extInvoiceId;
    }

    /**
     * @param string $extInvoiceId
     */
    public function setExtInvoiceId(string $extInvoiceId): void
    {
        $this->extInvoiceId = $extInvoiceId;
    }

    /**
     * @return int
     */
    public function getOwnerTeamId(): int
    {
        return $this->getClient()->getOwnerTeam()->getId();
    }

    /**
     * @return Team
     */
    public function getOwnerTeam(): Team
    {
        return $this->getClient()->getOwnerTeam();
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getExtPaymentId(): ?string
    {
        return $this->extPaymentId;
    }

    public function setExtPaymentId(?string $extPaymentId): self
    {
        $this->extPaymentId = $extPaymentId;

        return $this;
    }

    public function isExtPaid(): ?bool
    {
        return $this->extPaid;
    }

    public function setExtPaid(bool $extPaid): self
    {
        $this->extPaid = $extPaid;

        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @return float
     */
    public function getPaymentFee(): float
    {
        return $this->paymentFee;
    }

    /**
     * @param float $paymentFee
     */
    public function setPaymentFee(mixed $paymentFee): self
    {
        $this->paymentFee = $paymentFee;

        return $this;
    }

    public function getTotalAmountForPayment()
    {
        return $this->totalAmount + $this->paymentFee;
    }

    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @param InvoiceDetails[] $invoiceLineItems
     * @return float|int
     */
    public function calculateTax($invoiceLineItems)
    {
        if ($this->isWithoutTax()) {
            return 0;
        }

        $tax = 0;
        foreach ($invoiceLineItems as $lineItem) {
            $tax += ceil(($lineItem->getTotal() * self::GST_VALUE * 100) . '') / 100;
        }

        return $tax;
    }

    public function isPaid()
    {
        return $this->status == self::STATUS_PAID;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrepayment(): ?self
    {
        return $this->prepayment;
    }

    public function setPrepayment(?self $prepayment): self
    {
        $this->prepayment = $prepayment;

        return $this;
    }

    public function getTotalWithPrepayment()
    {
        $prepayment = $this->getPrepayment();
        $previousPrepayment = $this->getPreviousPrepayment();

        return round($this->getTotalAmount() + $prepayment?->getTotalAmount()
            - $previousPrepayment?->getTotalAmount(), 2);
    }

    public function getTotalTaxWithPrepayment()
    {
        $prepayment = $this->getPrepayment();
        $previousPrepayment = $this->getPreviousPrepayment();
        $amount = $this->getAmount() + $prepayment?->getAmount() - $previousPrepayment?->getAmount();

        if ($this->isWithoutTax()) {
            return 0;
        }

        return ceil(($amount * self::GST_VALUE * 100) . '') / 100;

    }

    public function getPreviousPrepayment(): ?self
    {
        return $this->previousPrepayment;
    }

    public function setPreviousPrepayment(?self $previousPrepayment): self
    {
        $this->previousPrepayment = $previousPrepayment;

        return $this;
    }

    private function isWithoutTax()
    {
        return strtolower($this->getClient()->getCountry()) !== strtolower(CountryHelper::COUNTRY_AUSTRALIA);
    }

    public function getAmountWithPrepayment()
    {
        $prepayment = $this->getPrepayment();
        $previousPrepayment = $this->getPreviousPrepayment();

        return round($this->getAmount() + $prepayment?->getAmount() - $previousPrepayment?->getAmount(), 2);
    }

    public function getInternalInvoiceId(): string
    {
        return 'LX-' . $this->getClient()->getId() . '-' . $this->getId();
    }

    public function getTeam(): Team
    {
        return $this->getClient()->getTeam();
    }

    public function getStripeFee(): float
    {
        return $this->stripeFee;
    }

    public function setStripeFee(float $fee): self
    {
        $this->stripeFee = $fee;

        return $this;
    }

    public function isFirstInvoice(): bool
    {
        return !$this->getDetails()->count() && !$this->getPreviousPrepayment() && $this->getPrepayment();
    }
}
