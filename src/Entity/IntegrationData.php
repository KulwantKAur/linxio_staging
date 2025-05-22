<?php

namespace App\Entity;

use App\Repository\IntegrationDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'integration_data')]
#[ORM\UniqueConstraint(columns: ['integration_id', 'team_id'])]
#[ORM\Entity(repositoryClass: IntegrationDataRepository::class)]
class IntegrationData extends BaseEntity
{
    public const STATUS_REQUIRE_DATA = 'require_data';

    public function __construct(array $fields)
    {
        $this->integration = $fields['integration'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->data = $fields['data'] ?? null;
        $this->scope = $fields['scope'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_DISABLED;
        $this->lastUpdatedAt = $fields['lastUpdateDate'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;
        $data['integration'] = $this->getIntegration()->toArray();
        $data['team'] = $this->getTeam()->toArray(['id']);
        $data['data'] = $this->getData();
        $data['scope'] = $this->getScope()->toArray();
        $data['status'] = $this->getStatus();
        $data['lastUpdatedAt'] = $this->formatDate($this->getLastUpdatedAt());

        return $data;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var Integration
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Integration')]
    #[ORM\JoinColumn(name: 'integration_id', referencedColumnName: 'id')]
    private $integration;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    #[ORM\Column(name: 'data', type: 'json', options: ['jsonb' => true], nullable: true)]
    private $data = [];

    /**
     * @var Team
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\IntegrationScope', inversedBy: 'integrationData')]
    #[ORM\JoinColumn(name: 'scope', referencedColumnName: 'id', nullable: true)]
    private $scope;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, options: ['default' => self::STATUS_ENABLED])]
    private string $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'last_updated_at', type: 'datetime', nullable: true)]
    private $lastUpdatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntegration(): Integration
    {
        return $this->integration;
    }

    public function setIntegration(Integration $integration): self
    {
        $this->integration = $integration;

        return $this;
    }


    /**
     * Get team id
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->team ? $this->team->getId() : null;
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
     * Set team
     *
     * @param Team $team
     *
     * @return self
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getScope(): IntegrationScope
    {
        return $this->scope;
    }

    public function setScope(IntegrationScope $integrationScope)
    {
        $this->scope = $integrationScope;

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

    public function isRequireData()
    {
        return $this->getStatus() === self::STATUS_REQUIRE_DATA;
    }

    /**
     * @param \DateTime $lastUpdatedAt
     *
     * @return IntegrationData
     */
    public function setLastUpdatedAt($lastUpdatedAt)
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdatedAt()
    {
        return $this->lastUpdatedAt;
    }
}
