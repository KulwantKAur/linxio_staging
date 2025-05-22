<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * AreaGroup
 */
#[ORM\Table(name: 'area_group')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\AreaGroupRepository')]
#[ORM\EntityListeners(['App\EventListener\AreaGroup\AreaGroupEntityListener'])]
class AreaGroup extends BaseEntity
{
    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'team',
        'status',
        'color',
        'createdAt',
        'updatedAt',
        'areasCount',
        'type',
        'readOnly'
    ];

    public const FULL_DISPLAY_VALUES = [
        'name',
        'status',
        'team',
        'color',
        'areas',
        'areasCount',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'type',
        'readOnly'
    ];

    public const TYPE_CUSTOM = 'custom';
    public const TYPE_FUEL_STATION = 'fuel_station';
    public const CHEVRON_DEFAULT_GROUP = 'Caltex Fuel Stations';

    /**
     * AreaGroup constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->color = $fields['color'] ?? null;
        $this->createdAt = new \DateTime();
        $this->areas = new ArrayCollection();
        $this->type = $fields['type'] ?? self::TYPE_CUSTOM;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->id
        ];
        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('color', $include, true)) {
            $data['color'] = $this->getColor();
        }
        if (in_array('areas', $include, true)) {
            $data['areas'] = $this->getAreasArray();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedByData();
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('areasCount', $include, true)) {
            $data['areasCount'] = $this->getAreasCount();
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if (in_array('readOnly', $include, true)) {
            $data['readOnly'] = $this->getIsReadOnly();
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
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'areaGroups')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: true)]
    private $status = self::STATUS_ACTIVE;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'color', type: 'string', length: 255, nullable: true)]
    private $color;

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
     * Many Groups have Many Areas.
     */
    #[ORM\JoinTable(name: 'areas_groups')]
    #[ORM\ManyToMany(targetEntity: 'Area', inversedBy: 'groups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $areas;

    /**
     * Many Area Groups have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'areaGroups', fetch: 'EXTRA_LAZY')]
    private $userGroups;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 100, options: ['default' => self::TYPE_CUSTOM])]
    private $type;

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
     * Set name.
     *
     * @param string $name
     *
     * @return AreaGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return AreaGroup
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

    /**
     * Set status.
     *
     * @param string|null $status
     *
     * @return AreaGroup
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set color.
     *
     * @param string|null $color
     *
     * @return AreaGroup
     */
    public function setColor($color = null)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color.
     *
     * @return string|null
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AreaGroup
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string
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
     * @return AreaGroup
     */
    public function setCreatedBy(User $createdBy)
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return AreaGroup
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
     * @return AreaGroup
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return array
     */
    public function getAreasArray()
    {
        return array_map(
            function ($area) {
                return $area->toArray(Area::DEFAULT_DISPLAY_VALUES);
            },
            $this->areas->toArray()
        );
    }

    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @return ArrayCollection
     */
    public function getAreasEntities()
    {
        return $this->areas;
    }

    /**
     * @return array
     */
    public function getAreaIds(): array
    {
        return $this->areas->map(
            function (Area $area) {
                return $area->getId();
            }
        )->toArray();
    }

    /**
     * @param Area $area
     */
    public function addArea(Area $area)
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
        }
    }

    /**
     * @param Area $area
     */
    public function removeArea(Area $area)
    {
        $this->areas->removeElement($area);
    }

    /**
     *
     */
    public function removeAllAreas()
    {
        $this->areas->clear();
    }

    /**
     * @return array|null
     */
    public function getUpdatedByData(): ?array
    {
        return $this->updatedBy ? $this->updatedBy->toArray(['id', 'fullName', 'teamType', 'email']) : null;
    }

    /**
     * @return array|null
     */
    public function getCreatedByData(): ?array
    {
        return $this->createdBy ? $this->createdBy->toArray(['id', 'fullName', 'teamType', 'email']) : null;
    }

    /**
     * @return int
     */
    public function getAreasCount(): int
    {
        return $this->areas->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('status', Area::STATUS_ACTIVE))
        )->count();
    }

    public function addToUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->add($userGroup);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function removeFromUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIsReadOnly(): bool
    {
        return in_array($this->getType(), [self::TYPE_FUEL_STATION]);
    }
}