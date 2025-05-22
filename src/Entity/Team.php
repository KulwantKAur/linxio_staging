<?php

namespace App\Entity;

use App\Entity\Xero\XeroClientAccount;
use App\Entity\Xero\XeroClientSecret;
use App\Mailer\MailSender;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Team
 */
#[ORM\Table(name: 'team')]
#[ORM\Entity(repositoryClass: 'App\Repository\TeamRepository')]
#[ORM\EntityListeners(['App\EventListener\Team\TeamEntityListener'])]
class Team extends BaseEntity
{
    public const TEAM_CLIENT = 'client';
    public const TEAM_ADMIN = 'admin';
    public const TEAM_RESELLER = 'reseller';
    public const TEAM_TYPES = [self::TEAM_CLIENT, self::TEAM_ADMIN, self::TEAM_RESELLER];
    public const DEFAULT_DISPLAY_VALUES = [
        'type',
        'clientId',
        'clientName',
        'resellerId',
        'resellerName',
    ];
    public const IS_CHEVRON_OPTION = 'isChevron';

    public function __construct(array $fields)
    {
        $this->setType($fields['type']);
        $this->users = new ArrayCollection();
        $this->devices = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->areaGroups = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->inspectionForms = new ArrayCollection();
        $this->sensors = new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->billingPlan = new ArrayCollection();
        $this->stripeSecret = new ArrayCollection();
        $this->xeroClientSecret = new ArrayCollection();
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->type;
        }

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient();
        }

        if (in_array('clientId', $include, true)) {
            $data['clientId'] = $this->getClientId();
        }

        if (in_array('resellerId', $include, true)) {
            $data['resellerId'] = $this->getResellerId();
        }

        if (in_array('resellerName', $include, true)) {
            $data['resellerName'] = $this->getResellerName();
        }

        if (in_array('clientName', $include, true)) {
            $data['clientName'] = $this->getClientName();
        }

        if (in_array('clientStatus', $include, true)) {
            $data['clientStatus'] = $this->getClient()?->getStatus();
        }

        if (in_array('blockedBillingAt', $include, true)) {
            $data['blockedBillingAt'] = $this->formatDate($this->getBlockedBillingDateAt());
        }

        if (in_array('options', $include, true)) {
            $data['options'] = $this->getOptions();
        }

        if (in_array('isChevron', $include, true)) {
            $data['isChevron'] = $this->isChevron();
        }
        if (in_array('chevronAccountId', $include, true)) {
            $data['chevronAccountId'] = $this->getChevronAccountId();
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
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    #[ORM\OneToMany(targetEntity: 'User', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $users;

    #[ORM\OneToMany(targetEntity: 'Client', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $client;

    #[ORM\OneToMany(targetEntity: 'Device', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $devices;

    #[ORM\OneToMany(targetEntity: 'Vehicle', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $vehicles;

    #[ORM\OneToMany(targetEntity: 'AreaGroup', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $areaGroups;

    #[ORM\OneToMany(targetEntity: 'Setting', mappedBy: 'team', fetch: 'EAGER', cascade: ['persist'])]
    private $settings;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\InspectionForm', mappedBy: 'teams', fetch: 'EXTRA_LAZY')]
    private $inspectionForms;

    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceSensor', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $sensors;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Asset', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $assets;

    #[ORM\OneToMany(targetEntity: 'App\Entity\VehicleType', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $vehicleTypes;

    #[ORM\OneToMany(targetEntity: 'Area', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $areas;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Xero\XeroClientAccount', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $xeroClientAccount;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Xero\XeroClientSecret', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $xeroClientSecret;

    #[ORM\OneToMany(targetEntity: 'App\Entity\StripeSecret', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $stripeSecret;

    private EntityManager $em;

    #[ORM\OneToMany(targetEntity: 'BillingPlan', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $billingPlan;

    #[ORM\Column(name: 'options', type: 'json', nullable: true, options: ['jsonb' => true])]
    private $options;

    #[ORM\OneToMany(targetEntity: 'FuelStation', mappedBy: 'team', fetch: 'EXTRA_LAZY')]
    private $fuelStation;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Client', mappedBy: 'ownerTeam', fetch: 'EXTRA_LAZY')]
    private $ownerTeam;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $type
     * @return $this
     * @throws \Exception
     */
    public function setType($type)
    {
        if (in_array($type, self::TEAM_TYPES)) {
            $this->type = $type;
        } else {
            throw new \Exception('Invalid team type');
        }

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return ArrayCollection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @return ArrayCollection
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }

    public function getXeroClientAccount(): XeroClientAccount
    {
        return $this->xeroClientAccount;
    }

    public function setXeroClientAccount(?XeroClientAccount $xeroClientAccount): self
    {
        $this->xeroClientAccount = $xeroClientAccount;

        return $this;
    }

    public function getXeroClientSecret(): ?XeroClientSecret
    {
        return $this->xeroClientSecret->first();
    }

    public function setXeroClientSecret(?XeroClientSecret $xeroClientSecret): self
    {
        $this->xeroClientSecret->add($xeroClientSecret);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAreaGroups()
    {
        return $this->areaGroups;
    }


    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->isClientTeam() ? $this->client->first() : null;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client->add($client);
    }

    public function getClientId()
    {
        return $this->isClientTeam() ? $this->getClient()->getId() : null;
    }

    public function getClientName()
    {
        return $this->isClientTeam() ? $this->getClient()->getName() : null;
    }

    public function getReseller(): ?Reseller
    {
        return $this->em->getRepository(Reseller::class)->findOneBy(['team' => $this]);
    }

    public function getResellerId()
    {
        return $this->isResellerTeam() ? $this->getReseller()->getId() : null;
    }

    public function getResellerName()
    {
        return $this->isResellerTeam() ? $this->getReseller()->getCompanyName() : null;
    }

    /**
     * @return string|null
     */
    public function getResellerCompany()
    {
        return ($this->isResellerTeam() && $this->getReseller()) ? $this->getReseller()->getCompanyName() : null;
    }

    /**
     * @return Team
     */
    public static function createNewClientTeam()
    {
        return new self(['type' => self::TEAM_CLIENT]);
    }

    /**
     * @return Team
     */
    public static function createNewAdminTeam()
    {
        return new self(['type' => self::TEAM_ADMIN]);
    }

    /**
     * @return Team
     */
    public static function createNewResellerTeam()
    {
        return new self(['type' => self::TEAM_RESELLER]);
    }

    /**
     * @return bool
     */
    public function isClientTeam(): bool
    {
        return $this->type === self::TEAM_CLIENT;
    }

    /**
     * @return bool
     */
    public function isAdminTeam(): bool
    {
        return $this->type === self::TEAM_ADMIN;
    }

    public function isResellerTeam(): bool
    {
        return $this->type === self::TEAM_RESELLER;
    }

    public function getSettings()
    {
        return $this->settings ?? new ArrayCollection();
    }

    /**
     * @param $name
     * @return Setting|null
     */
    public function getSettingsByName($name)
    {
        $settingName = is_array($name) ? $name : [$name];
        $settings = $this->getSettings()->matching(
            Criteria::create()->andWhere(Criteria::expr()->in('name', $settingName))
                ->andWhere(Criteria::expr()->isNull('role'))
                ->andWhere(Criteria::expr()->isNull('user'))
        );

        if (is_array($name)) {
            return $settings;
        }

        return $settings->count() ? $settings->first() : null;
    }

    /**
     * @param string $name
     * @return Setting|null
     */
    public function getSettingByName(string $name): ?Setting
    {
        $settings = $this->getSettings()->matching(
            Criteria::create()->andWhere(Criteria::expr()->eq('name', $name))
        );

        return $settings->count() ? $settings->first() : null;
    }

    /**
     * @param InspectionForm $inspectionForm
     * @return $this
     */
    public function removeFromInspectionForm(InspectionForm $inspectionForm)
    {
        $this->inspectionForms->removeElement($inspectionForm);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeFromAllInspectionForms()
    {
        foreach ($this->inspectionForms as $inspectionForm) {
            $inspectionForm->removeTeam($this);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getSensors(): ?ArrayCollection
    {
        return $this->sensors;
    }

    public function getAssets(): ?ArrayCollection
    {
        return $this->assets;
    }

    public function getPlatformSetting(): ?PlatformSetting
    {
        return $this->em->getRepository(PlatformSetting::class)->findOneBy(['team' => $this]);
    }

    public function getBillingSetting(): ?BillingSetting
    {
        return $this->em->getRepository(BillingSetting::class)->findOneBy(['team' => $this]);
    }

    public function getAreas()
    {
        return $this->areas;
    }

    public function getPlatformSettingByTeam(): ?PlatformSetting
    {
        if ($this->isClientTeam() && $this->getClient()->getReseller()) {
            return $this->getClient()->getReseller()->getTeam()->getPlatformSetting();
        } elseif ($this->isAdminTeam() || $this->isResellerTeam()) {
            return $this->getPlatformSetting();
        } else {
            $adminTeam = $this->em->getRepository(Team::class)->findOneBy(['type' => Team::TEAM_ADMIN]);
            if ($adminTeam) {
                return $adminTeam->getPlatformSettingByTeam();
            }
        }

        return null;
    }

    public function getNotificationEmail(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getNotificationEmail() : null;
    }

    public function getSmsName(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getSmsName() : null;
    }

    public function getEmailName(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getEmailName() : null;
    }

    public function getLogoPath(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getLogoPath() : null;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getDomain() : null;
    }

    public function getHostApp(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getHostApp() : null;
    }

    public function getSupportEmail(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getSupportEmail() : null;
    }

    public function getProductName(): ?string
    {
        return $this->getPlatformSettingByTeam() ? $this->getPlatformSettingByTeam()->getProductName() : null;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    public function getBillingPlan()
    {
        $activeElement = $this->billingPlan->filter(function ($element) {
            return $element->getStatus() == BaseEntity::STATUS_ACTIVE;
        });

        return $activeElement?->first() ? $activeElement->first() : null;
    }

    public function setBillingPlan(BillingPlan $billingPlan): self
    {
        $this->billingPlan->add($billingPlan);

        return $this;
    }

    /**
     * @return StripeSecret
     */
    public function getStripeSecret()
    {
        return $this->stripeSecret->first();
    }

    /**
     * @param mixed $stripeSecret
     */
    public function setStripeSecret($stripeSecret): void
    {
        $this->stripeSecret->add($stripeSecret);
    }

    public function getBlockedBillingDateAt(): ?\DateTime
    {
        if (!$this->isClientTeam() || !$this->getClient()->getOldestOverdueInvoice()) {
            return null;
        }

        return Carbon::parse($this->getClient()->getOldestOverdueInvoice()->getDueAt())->addDays(Invoice::OVERDUE_BLOCKED_DAYS);
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): mixed
    {
        return $this->options;
    }

    public function isChevron(): bool
    {
        return ($this->getOptions()[self::IS_CHEVRON_OPTION] ?? false);
    }

    public function setAsChevron(): self
    {
        $this->options[self::IS_CHEVRON_OPTION] = true;

        return $this;
    }

    public function getEmailTranslateFilename(): string
    {
        if ($this->isChevron()) {
            return 'email_chevron';
        }

        return MailSender::EMAIL_TRANSLATE_DOMAIN;
    }

    /**
     * @return string|null
     */
    public function getChevronAccountId(): ?string
    {
        return $this->getClient()?->getChevronAccountId();
    }

    public function getTimezone(): ?TimeZone
    {
        $timeZoneSetting = $this->getSettingsByName(Setting::TIMEZONE_SETTING);
        return $timeZoneSetting
            ? $this->em->getRepository(TimeZone::class)->find($timeZoneSetting->getValue())
            : null;
    }

    public function getTimezoneText(): ?string
    {
        $offset = $this->getTimezone() ? Carbon::now($this->getTimezone()->getName())->getOffsetString() : null;

        return '(UTC' . $offset . ') ' . $this->getTimezone()?->getName();
    }

    public function getTimezoneName(): ?string
    {
        return $this->getTimezone()?->getName();
    }

    public function getDateFormatSettingConverted(bool $time = false)
    {
        $settingFormat = $this->getDateFormatSetting();
        $timeFormat = $this->getTimeFormatSetting();

        if (isset(Setting::DATE_FORMAT_VALUES[$settingFormat])) {
            return $time ? Setting::DATE_FORMAT_VALUES[$settingFormat] . ' ' . $timeFormat : Setting::DATE_FORMAT_VALUES[$settingFormat];
        } else {
            return $time ? BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT . ' ' . $timeFormat : BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT;
        }
    }

    public function getDateFormatSetting()
    {
        $dateTimeSetting = $this->em->getRepository(Setting::class)->getSetting(Setting::DATE_FORMAT, $this);

        return $dateTimeSetting !== null ? $dateTimeSetting->getValue() : Setting::DATE_FORMAT_VALUE;
    }

    public function getTimeFormatSetting()
    {
        $dateTimeSetting = $this->em->getRepository(Setting::class)->getSetting(Setting::TIME_12H, $this);

        return $dateTimeSetting?->getValue() ? 'h:i a' : 'H:i';
    }
}
