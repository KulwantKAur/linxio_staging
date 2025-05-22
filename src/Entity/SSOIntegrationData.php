<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields={"idpEntityId"},
 *     message="idpEntityId is already exists."
 * )
 */
#[ORM\Table(name: 'sso_integration_data')]
#[ORM\Entity(repositoryClass: 'App\Repository\SSOIntegrationDataRepository')]
class SSOIntegrationData extends BaseEntity
{
    use AttributesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\SSOIntegration')]
    #[ORM\JoinColumn(name: 'integration_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SSOIntegration $integration;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Team $team;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private ?string $name;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'idp_entity_id', type: 'text', unique: true, nullable: false)]
    private string $idpEntityId;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'idp_sso_url', type: 'text', nullable: false)]
    private string $idpSSOUrl;

    #[ORM\Column(name: 'idp_slo_url', type: 'text', nullable: true)]
    private ?string $idpSLOUrl;

    /**
     * @var Collection|SSOIntegrationCertificate[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\SSOIntegrationCertificate', mappedBy: 'integrationData', fetch: 'EXTRA_LAZY')]
    private $certificates;

    #[ORM\Column(name: 'options', type: 'json', options: ['jsonb' => true], nullable: true)]
    private ?array $options;

    #[ORM\Column(name: 'settings', type: 'json', options: ['jsonb' => true], nullable: true)]
    private ?array $settings;

    #[ORM\Column(name: 'status', type: 'string', length: 255, options: ['default' => self::STATUS_ENABLED])]
    private string $status;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\OneToMany(mappedBy: 'SSOIntegrationData', targetEntity: 'User', fetch: 'EXTRA_LAZY')]
    private $users;

    #[ORM\Column(name: 'is_allow_direct_login', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isAllowDirectLogin;

    #[ORM\Column(name: 'is_only_auth', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isOnlyAuth;

    public function __construct(array $fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->idpEntityId = $fields['idpEntityId'];
        $this->idpSSOUrl = $fields['idpSSOUrl'];
        $this->idpSLOUrl = $fields['idpSLOUrl'] ?? null;
        $this->certificates = new ArrayCollection();
        $this->team = $fields['team'] ?? null;
        $this->options = $fields['options'] ?? null;
        $this->settings = $fields['settings'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ENABLED;
        $this->updatedAt = $fields['updatedAt'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new Carbon();
        $this->isAllowDirectLogin = $fields['isAllowDirectLogin'] ?? false;
        $this->isOnlyAuth = $fields['isAllowDirectLogin'] ?? false;
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
        $data['integration'] = $this->getIntegration()->toArray();
        $data['idpEntityId'] = $this->getIdpEntityId();
        $data['idpSSOUrl'] = $this->getIdpSSOUrl();
        $data['idpSLOUrl'] = $this->getIdpSLOUrl();
        $data['team'] = $this->getTeam()?->toArray(['id']);
        $data['options'] = $this->getOptions();
        $data['settings'] = $this->getSettings();
        $data['status'] = $this->getStatus();
        $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        $data['certificates'] = $this->getCertificatesArray();
        $data['isAllowDirectLogin'] = $this->isAllowDirectLogin();

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntegration(): SSOIntegration
    {
        return $this->integration;
    }

    public function setIntegration(SSOIntegration $integration): self
    {
        $this->integration = $integration;

        return $this;
    }

    public function getIntegrationName(): string
    {
        return $this->getIntegration()->getName();
    }

    public function getTeamId()
    {
        return $this->getTeam()?->getId();
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array|null $options
     * @return $this
     */
    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isEnabled()
    {
        return $this->getStatus() === self::STATUS_ENABLED;
    }

    public function isDisabled()
    {
        return $this->getStatus() === self::STATUS_DISABLED;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getIdpEntityId(): string
    {
        return $this->idpEntityId;
    }

    /**
     * @param string $idpEntityId
     */
    public function setIdpEntityId(string $idpEntityId): void
    {
        $this->idpEntityId = $idpEntityId;
    }

    /**
     * @return string
     */
    public function getIdpSSOUrl(): string
    {
        return $this->idpSSOUrl;
    }

    /**
     * @param string $idpSSOUrl
     */
    public function setIdpSSOUrl(string $idpSSOUrl): void
    {
        $this->idpSSOUrl = $idpSSOUrl;
    }

    /**
     * @return string|null
     */
    public function getIdpSLOUrl(): ?string
    {
        return $this->idpSLOUrl;
    }

    /**
     * @param string|null $idpSLOUrl
     */
    public function setIdpSLOUrl(?string $idpSLOUrl): void
    {
        $this->idpSLOUrl = $idpSLOUrl;
    }

    /**
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * @param array|null $settings
     */
    public function setSettings(?array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     */
    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
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
     * @return SSOIntegrationCertificate[]|Collection|null
     */
    public function getCertificates(): Collection|null
    {
        return $this->certificates;
    }

    /**
     * @return SSOIntegrationCertificate[]|Collection|null
     */
    public function getEnabledCertificates(): Collection|null
    {
        return $this->getCertificates()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('status', self::STATUS_ENABLED))
        );
    }

    /**
     * @return SSOIntegrationCertificate|null
     */
    public function getLastEnabledCertificate(): ?SSOIntegrationCertificate
    {
        return $this->getCertificates()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('status', self::STATUS_ENABLED))
                ->orderBy(['createdAt' => Criteria::DESC])
        )->first() ?: null;
    }

    /**
     * @return array
     */
    public function getCertificatesArray(): array
    {
        return array_values(
            $this->getCertificates()->map(
                static function (SSOIntegrationCertificate $certificate) {
                    return $certificate->toArray();
                }
            )->toArray()
        );
    }

    /**
     * @param SSOIntegrationCertificate[]|Collection|null $certificates
     */
    public function setCertificates(Collection|null $certificates): void
    {
        $this->certificates = $certificates;
    }

    /**
     * @param SSOIntegrationCertificate $certificate
     */
    public function addCertificate(SSOIntegrationCertificate $certificate): void
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
        }
    }

    /**
     * @param SSOIntegrationCertificate $certificate
     */
    public function removeCertificate(SSOIntegrationCertificate $certificate): void
    {
        $this->certificates->removeElement($certificate);
    }

    /**
     * @return string|null
     */
    public function getTeamType(): ?string
    {
        return $this->getTeam()?->getType();
    }

    /**
     * @return bool
     */
    public function isClientTeamType(): bool
    {
        return $this->getTeam()?->isClientTeam();
    }

    /**
     * @return bool
     */
    public function isResellerTeamType(): bool
    {
        return $this->getTeam()?->isResellerTeam();
    }

    public function isAllowDirectLogin(): bool
    {
        return $this->isAllowDirectLogin;
    }

    public function setIsAllowDirectLogin(bool $isAllowDirectLogin): void
    {
        $this->isAllowDirectLogin = $isAllowDirectLogin;
    }

    public function isOnlyAuth(): bool
    {
        return $this->isOnlyAuth;
    }

    public function setIsOnlyAuth(bool $isOnlyAuth): void
    {
        $this->isOnlyAuth = $isOnlyAuth;
    }
}

