<?php

namespace App\Entity;

use App\Repository\BillingEntityHistoryRepository;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'billing_entity_history')]
#[ORM\Index(name: 'billing_entity_history_entity_entity_id_type_index', columns: ['entity_id', 'entity', 'type'])]
#[ORM\Entity(repositoryClass: BillingEntityHistoryRepository::class)]
class BillingEntityHistory extends BaseEntity
{
    use AttributesTrait;

    public const TYPE_CREATE_DELETE = 'create_delete';
    public const TYPE_CHANGE_TEAM = 'change_team';
    public const TYPE_UNAVAILABLE = 'unavailable';
    public const TYPE_DEACTIVATED = 'deactivated';
    public const TYPE_CHANGE_STATUS = 'change_status';
    public const TYPE_ARCHIVE = 'archive';

    public const ENTITY_VEHICLE = 'vehicle';
    public const ENTITY_DEVICE = 'device';
    public const ENTITY_CLIENT = 'client';

    public const TYPES_FOR_CHECK_BEFORE_RECREATE = [self::TYPE_DEACTIVATED, self::TYPE_UNAVAILABLE];

    public function __construct(array $fields)
    {
        $this->entityId = $fields['entityId'] ?? null;
        $this->entity = $fields['entity'] ?? null;
        $this->type = $fields['type'] ?? null;
        $this->dateFrom = $fields['dateFrom'] ?? null;
        $this->dateTo = $fields['dateTo'] ?? null;
        $this->data = $fields['data'] ?? null;
        $this->team = $fields['team'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'entityId' => $this->getEntityId(),
            'type' => $this->getType(),
            'dateFrom' => $this->getDateFrom()?->format('c'),
            'dateTo' => $this->getDateTo()?->format('c'),
            'team' => $this->getTeam()->toArray(['clientId', 'type', 'clientName'])
        ];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $entityId;

    #[ORM\Column(type: 'string', length: 255)]
    private $entity;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\Column(type: 'datetime')]
    private $dateFrom;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $dateTo;

    #[ORM\Column(type: 'json', nullable: true)]
    private $data = [];

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
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

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(\DateTimeInterface $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTimeInterface $dateTo): self
    {
        $this->dateTo = $dateTo;

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

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return BillingEntityHistory
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}
