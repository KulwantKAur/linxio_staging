<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTrait;
use App\Entity\Traits\UpdatedTrait;
use App\Service\File\LocalFileService;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PlatformSetting
 */
#[ORM\Table(name: 'platform_setting')]
#[ORM\Entity(repositoryClass: 'App\Repository\PlatformSettingRepository')]
class PlatformSetting extends BaseEntity
{
    use CreatedTrait;
    use UpdatedTrait;
    use AttributesTrait;

    public const DEFAULT_ACCOUNTING_EMAIL = 'accounts@linxio.com';

    public const DEFAULT_DISPLAY_VALUES = [
        'currency',
        'logo',
        'domain',
        'units',
        'supportPhone',
        'supportMsg',
        'supportEmail',
        'clientDefaultTheme',
        'productName',
        'favicon',
        'smsName',
        'emailName',
        'notificationEmail',
        'linkAppStore',
        'linkPlayMarket',
        'linkKnowledgeBase',
        'intercomId',
        'accountingEmail',
        'hostApi',
        'hostApp',
        'hostTrack',
        'hostMessenger',
        'salesEmail',
        'privacyPolicyLink',
        'termsOfUseLink',
    ];

    public function __construct(array $fields)
    {
        $this->currency = $fields['currency'] ?? null;
        $this->logo = $fields['logo'] ?? null;
        $this->domain = $fields['domain'] ?? null;
        $this->units = $fields['units'] ?? null;
        $this->supportPhone = $fields['supportPhone'] ?? null;
        $this->smsName = $fields['smsName'] ?? null;
        $this->emailName = $fields['emailName'] ?? null;
        $this->productName = $fields['productName'] ?? null;
        $this->favicon = $fields['favicon'] ?? null;
        $this->supportMsg = $fields['supportMsg'] ?? null;
        $this->supportEmail = $fields['supportEmail'] ?? null;
        $this->clientDefaultTheme = $fields['clientDefaultTheme'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->notificationEmail = $fields['notificationEmail'] ?? null;
        $this->linkAppStore = $fields['linkAppStore'] ?? null;
        $this->linkPlayMarket = $fields['linkPlayMarket'] ?? null;
        $this->linkKnowledgeBase = $fields['linkKnowledgeBase'] ?? null;
        $this->intercomId = $fields['intercomId'] ?? null;
        $this->accountingEmail = $fields['accountingEmail'] ?? self::DEFAULT_ACCOUNTING_EMAIL;
        $this->hostApi = $fields['hostApi'] ?? null;
        $this->hostApp = $fields['hostApp'] ?? null;
        $this->hostTrack = $fields['hostTrack'] ?? null;
        $this->hostMessenger = $fields['hostMessenger'] ?? null;
        $this->salesEmail = $fields['salesEmail'] ?? null;
        $this->privacyPolicyLink = $fields['privacyPolicyLink'] ?? null;
        $this->termsOfUseLink = $fields['termsOfUseLink'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('currency', $include, true)) {
            $data['currency'] = $this->getCurrency()?->toArray();
        }
        if (in_array('logo', $include, true)) {
            $data['logo'] = $this->getLogoData();
        }
        if (in_array('domain', $include, true)) {
            $data['domain'] = $this->getDomain();
        }
        if (in_array('units', $include, true)) {
            $data['units'] = $this->getUnits();
        }
        if (in_array('supportPhone', $include, true)) {
            $data['supportPhone'] = $this->getSupportPhone();
        }
        if (in_array('emailName', $include, true)) {
            $data['emailName'] = $this->getEmailName();
        }
        if (in_array('smsName', $include, true)) {
            $data['smsName'] = $this->getSmsName();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
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
        if (in_array('favicon', $include, true)) {
            $data['favicon'] = $this->getFaviconData();
        }
        if (in_array('supportPhone', $include, true)) {
            $data['supportPhone'] = $this->getSupportPhone();
        }
        if (in_array('supportMsg', $include, true)) {
            $data['supportMsg'] = $this->getSupportMsg();
        }
        if (in_array('supportEmail', $include, true)) {
            $data['supportEmail'] = $this->getSupportEmail();
        }
        if (in_array('clientDefaultTheme', $include, true)) {
            $data['clientDefaultTheme'] = $this->getClientDefaultTheme()?->toArray();
        }
        if (in_array('productName', $include, true)) {
            $data['productName'] = $this->getProductName();
        }
        if (in_array('notificationEmail', $include, true)) {
            $data['notificationEmail'] = $this->getNotificationEmail();
        }
        if (in_array('linkAppStore', $include, true)) {
            $data['linkAppStore'] = $this->getLinkAppStore();
        }
        if (in_array('linkPlayMarket', $include, true)) {
            $data['linkPlayMarket'] = $this->getLinkPlayMarket();
        }
        if (in_array('linkKnowledgeBase', $include, true)) {
            $data['linkKnowledgeBase'] = $this->getLinkKnowledgeBase();
        }
        if (in_array('intercomId', $include, true)) {
            $data['intercomId'] = $this->getIntercomId();
        }
        if (in_array('accountingEmail', $include, true)) {
            $data['accountingEmail'] = $this->getAccountingEmail();
        }
        if (in_array('hostApi', $include, true)) {
            $data['hostApi'] = $this->getHostApi();
        }
        if (in_array('hostApp', $include, true)) {
            $data['hostApp'] = $this->getHostApp();
        }
        if (in_array('hostTrack', $include, true)) {
            $data['hostTrack'] = $this->getHostTrack();
        }
        if (in_array('hostMessenger', $include, true)) {
            $data['hostMessenger'] = $this->getHostMessenger();
        }
        if (in_array('salesEmail', $include, true)) {
            $data['salesEmail'] = $this->getSalesEmail();
        }
        if (in_array('isChevron', $include, true)) {
            $data['isChevron'] = $this->getIsChevron();
        }
        if (in_array('privacyPolicyLink', $include, true)) {
            $data['privacyPolicyLink'] = $this->getPrivacyPolicyLink();
        }
        if (in_array('termsOfUseLink', $include, true)) {
            $data['termsOfUseLink'] = $this->getTermsOfUseLink();
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var Currency|null
     */
    #[ORM\ManyToOne(targetEntity: 'Currency')]
    #[ORM\JoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: true)]
    private $currency;

    /**
     * @var File|null
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'logo_id', referencedColumnName: 'id')]
    private $logo;

    /**
     * @var string|null
     * @Assert\Length(max=100)
     */
    #[ORM\Column(name: 'domain', type: 'string', length: 100, nullable: true, unique: true)]
    private $domain;

    /**
     * @var string|null
     * @Assert\Length(max=30)
     */
    #[ORM\Column(name: 'units', type: 'string', length: 30, nullable: true)]
    private $units;

    /**
     * @var string|null
     * @Assert\Length(max=50)
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 50, nullable: true)]
    private $supportPhone;

    /**
     * @var string|null
     * @Assert\Length(max=200)
     */
    #[ORM\Column(name: 'sms_name', type: 'string', length: 200, nullable: true)]
    private $smsName;

    /**
     * @var string|null
     * @Assert\Length(max=200)
     */
    #[ORM\Column(name: 'email_name', type: 'string', length: 200, nullable: true)]
    private $emailName;

    /**
     * @var Team
     */
    #[ORM\OneToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string|null
     * @Assert\Length(max=200)
     */
    #[ORM\Column(name: 'product_name', type: 'string', length: 200, nullable: true)]
    private $productName;

    /**
     * @var File|null
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'favicon_id', referencedColumnName: 'id')]
    private $favicon;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'support_msg', type: 'text', nullable: true)]
    private $supportMsg;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'support_email', type: 'string', length: 200, nullable: true)]
    private $supportEmail;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'Theme')]
    #[ORM\JoinColumn(name: 'client_default_theme_id', referencedColumnName: 'id')]
    private $clientDefaultTheme;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'notification_email', type: 'string', length: 200, nullable: true)]
    private $notificationEmail;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'link_app_store', type: 'string', length: 200, nullable: true)]
    private $linkAppStore;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'link_play_market', type: 'string', length: 200, nullable: true)]
    private $linkPlayMarket;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'link_knowledge_base', type: 'string', nullable: true)]
    private $linkKnowledgeBase;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'intercom_id', type: 'string', nullable: true)]
    private $intercomId;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'accounting_email', type: 'string', length: 200, nullable: true)]
    private $accountingEmail;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'host_app', type: 'string', nullable: true)]
    private $hostApp;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'host_api', type: 'string', nullable: true)]
    private $hostApi;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'host_track', type: 'string', nullable: true)]
    private $hostTrack;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'host_messenger', type: 'string', nullable: true)]
    private $hostMessenger;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'sales_email', type: 'string', length: 200, nullable: true)]
    private $salesEmail;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'privacy_policy_link', type: 'string', length: 200, nullable: true)]
    private $privacyPolicyLink;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'terms_of_use_link', type: 'string', length: 200, nullable: true)]
    private $termsOfUseLink;

    /**
     * Set currency.
     *
     * @param Currency|null $currency
     *
     * @return PlatformSetting
     */
    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency.
     *
     * @return Currency|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set logoId.
     *
     * @param File|null $logo
     *
     * @return PlatformSetting
     */
    public function setLogo(File $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return File|null
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getLogoData()
    {
        if ($this->logo) {
            $this->logo->setPath(LocalFileService::RESELLER_LOGO_PATH_PUBLIC_PATH);

            return $this->logo->toArray();
        }

        return null;
    }

    public function getLogoPath()
    {
        if ($this->logo) {
            return '/' . LocalFileService::RESELLER_LOGO_PATH_PUBLIC_PATH . $this->getLogo()->getName();
        }

        return null;
    }

    /**
     * Set domain.
     *
     * @param string|null $domain
     *
     * @return PlatformSetting
     */
    public function setDomain($domain = null)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string|null
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param $units
     */
    public function setUnits($units)
    {
        $this->units = $units;
    }

    /**
     * @return string|null
     */
    public function getSupportPhone()
    {
        return $this->supportPhone;
    }

    public function setSupportPhone($phone)
    {
        $this->supportPhone = $phone;
    }

    /**
     * @return string|null
     */
    public function getSmsName()
    {
        return $this->smsName;
    }

    /**
     * @param $smsName
     */
    public function setSmsName($smsName)
    {
        $this->smsName = $smsName;
    }

    /**
     * @return string|null
     */
    public function getEmailName()
    {
        return $this->emailName;
    }

    /**
     * @param $emailName
     */
    public function setEmailName($emailName)
    {
        $this->emailName = $emailName;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return PlatformSetting
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

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getFavicon(): ?File
    {
        return $this->favicon;
    }

    public function getFaviconData()
    {
        if ($this->favicon) {
            $this->favicon->setPath(LocalFileService::RESELLER_LOGO_PATH_PUBLIC_PATH);

            return $this->favicon->toArray();
        }

        return null;
    }

    public function setFavicon(?File $favicon): self
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getSupportMsg(): ?string
    {
        return $this->supportMsg;
    }

    public function setSupportMsg(?string $supportMsg): self
    {
        $this->supportMsg = $supportMsg;

        return $this;
    }

    public function getSupportEmail(): ?string
    {
        return $this->supportEmail;
    }

    public function setSupportEmail(?string $supportEmail): self
    {
        $this->supportEmail = $supportEmail;

        return $this;
    }

    public function getClientDefaultTheme(): ?Theme
    {
        return $this->clientDefaultTheme;
    }

    public function setClientDefaultTheme(?Theme $clientDefaultTheme): self
    {
        $this->clientDefaultTheme = $clientDefaultTheme;

        return $this;
    }

    public function getNotificationEmail(): ?string
    {
        return $this->notificationEmail;
    }

    public function setNotificationEmail(?string $notificationEmail): self
    {
        $this->notificationEmail = $notificationEmail;

        return $this;
    }

    public function getLinkAppStore()
    {
        return $this->linkAppStore;
    }

    public function setLinkAppStore(?string $linkAppStore): self
    {
        $this->linkAppStore = $linkAppStore;

        return $this;
    }

    public function getLinkPlayMarket(): ?string
    {
        return $this->linkPlayMarket;
    }

    public function setLinkPlayMarket(?string $linkPlayMarket): self
    {
        $this->linkPlayMarket = $linkPlayMarket;

        return $this;
    }

    public function getLinkKnowledgeBase(): ?string
    {
        return $this->linkKnowledgeBase;
    }

    public function setLinkKnowledgeBase(?string $linkKnowledgeBase): self
    {
        $this->linkKnowledgeBase = $linkKnowledgeBase;

        return $this;
    }

    public function getIntercomId(): ?string
    {
        return $this->intercomId;
    }

    public function setIntercomId(?string $intercomId): self
    {
        $this->intercomId = $intercomId;

        return $this;
    }

    public function getAccountingEmail(): ?string
    {
        return $this->accountingEmail;
    }

    public function setAccountingEmail($email): self
    {
        $this->accountingEmail = $email;

        return $this;
    }

    public function getHostApp(): ?string
    {
        return $this->hostApp;
    }

    public function getHostApi(): ?string
    {
        return $this->hostApi;
    }

    public function getHostTrack(): ?string
    {
        return $this->hostTrack;
    }

    public function getHostMessenger(): ?string
    {
        return $this->hostMessenger;
    }

    public function setHostApi(?string $hostApi): self
    {
        $this->hostApi = $hostApi;

        return $this;
    }

    public function setHostApp(?string $hostApp): self
    {
        $this->hostApp = $hostApp;

        return $this;
    }

    public function setHostTrack(?string $hostTrack): self
    {
        $this->hostTrack = $hostTrack;

        return $this;
    }

    public function setHostMessenger(?string $hostMessenger): self
    {
        $this->hostMessenger = $hostMessenger;

        return $this;
    }

    public function getSalesEmail(): ?string
    {
        return $this->salesEmail;
    }

    public function setSalesEmail(?string $email): self
    {
        $this->salesEmail = $email;

        return $this;
    }

    public function getIsChevron(): bool
    {
        return (bool)$this->getTeam()?->isChevron();
    }

    public function getPrivacyPolicyLink(): ?string
    {
        return $this->privacyPolicyLink;
    }

    public function setPrivacyPolicyLink(?string $value): self
    {
        $this->privacyPolicyLink = $value;

        return $this;
    }

    public function getTermsOfUseLink(): ?string
    {
        return $this->termsOfUseLink;
    }

    public function setTermsOfUseLink(?string $value): self
    {
        $this->termsOfUseLink = $value;

        return $this;
    }
}
