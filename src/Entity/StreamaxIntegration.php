<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields={"tenantId"},
 *     message="tenantId is already exists."
 * )
 */
#[ORM\Table(name: 'streamax_integration')]
#[ORM\UniqueConstraint(columns: ['tenant_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\StreamaxIntegrationRepository')]
class StreamaxIntegration extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'team',
        'status',
        'updatedAt',
        'createdAt',
    ];

    public const DEFAULT_FULL_VALUES = [
        'name',
        'team',
        'status',
        'updatedAt',
        'createdAt',
        'url',
        'tenantId',
        'signature',
        'secret',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Team $team;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private ?string $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'url', type: 'text', nullable: false)]
    private string $url;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'signature', type: 'text', nullable: false)]
    private string $signature;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'tenant_id', type: 'string', length: 255, nullable: false)]
    private string $tenantId;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'secret', type: 'text', nullable: true)]
    private ?string $secret;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, options: ['default' => self::STATUS_ENABLED])]
    private string $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    /**
     * @var ArrayCollection|Device[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Device', mappedBy: 'streamaxIntegration', fetch: 'EXTRA_LAZY')]
    private $devices;

    public function __construct(array $fields)
    {
        $this->url = $fields['url'];
        $this->tenantId = $fields['tenantId'];
        $this->name = $fields['name'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ENABLED;
        $this->updatedAt = $fields['updatedAt'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new Carbon();

        if (isset($fields['signature'])) {
            $this->setSignature($fields['signature']);
        }
        if (isset($fields['secret'])) {
            $this->setSecret($fields['secret']);
        }
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

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('url', $include, true)) {
            $data['url'] = $this->getUrl();
        }
        if (in_array('tenantId', $include, true)) {
            $data['tenantId'] = $this->getTenantId();
        }
        if (in_array('signature', $include, true)) {
            $data['signature'] = $this->getSignature();
        }
        if (in_array('secret', $include, true)) {
            $data['secret'] = $this->getSecret();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()?->toArray(['id']);
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get team id
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()?->getId();
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

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return base64_decode($this->signature);
    }

    /**
     * @param string $signature
     */
    public function setSignature(string $signature): void
    {
        $this->signature = base64_encode($signature);
    }

    /**
     * @return string
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * @param string $tenantId
     */
    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret ? base64_decode($this->secret) : $this->secret;
    }

    /**
     * @param string|null $secret
     */
    public function setSecret(?string $secret): void
    {
        $this->secret = $secret ? base64_encode($secret) : $secret;
    }
}

