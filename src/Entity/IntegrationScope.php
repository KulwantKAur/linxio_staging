<?php

namespace App\Entity;

use App\Repository\IntegrationScopeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntegrationScopeRepository::class)]
class IntegrationScope extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'type',
        'value'
    ];

    public const ANY_SCOPE = 'all';
    public const VEHICLE_SCOPE = 'vehicle';
    public const DEPOT_SCOPE = 'depot';
    public const GROUP_SCOPE = 'group';

    public function __construct(array $fields)
    {
        $this->integration = $fields['integration'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->type = $fields['type'] ?? self::ANY_SCOPE;
        $this->value = $fields['value'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('integration', $include, true)) {
            $data['integration'] = $this->getIntegration()->toArray();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray(['id']);
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if (in_array('value', $include, true)) {
            $data['value'] = $this->getValue();
        }

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
    #[ORM\JoinColumn(name: 'integration_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $integration;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'scope_type', type: 'string', nullable: false)]
    private $type;

    #[ORM\Column(type: 'json', nullable: true)]
    private $value = [];

    /**
     * @var Asset|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\IntegrationData', mappedBy: 'scope', fetch: 'EXTRA_LAZY')]
    private $integrationData;

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

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(?array $value): self
    {
        $this->value = $value;

        return $this;
    }
}
