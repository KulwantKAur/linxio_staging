<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Area
 */
#[ORM\Table(name: 'area')]
#[ORM\Entity(repositoryClass: 'App\Repository\AreaRepository')]
#[ORM\EntityListeners(['App\EventListener\Area\AreaEntityListener'])]
class Area extends BaseEntity
{
    use AttributesTrait;

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'polygon',
        'coordinates',
        'status',
        'groups',
        'team',
        'createdAt',
        'updatedAt',
        'type',
        'readOnly'
    ];

    public const AREA_HISTORY_DISPLAY_VALUES = [
        'name',
//        'coordinates',
        'status',
        'color'
    ];

    public const TYPE_CUSTOM = 'custom';
    public const TYPE_FUEL_STATION = 'fuel_station';

    public function __construct(array $fields = [])
    {
        $this->name = $fields['name'] ?? null;
        $this->polygon = $fields['polygon'] ?? null;
        $this->coordinates = $fields['coordinates'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->groups = new ArrayCollection();
        $this->type = $fields['type'] ?? self::TYPE_CUSTOM;
        $this->externalId = $fields['externalId'] ?? null;
    }

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
        if (in_array('polygon', $include, true)) {
            $data['polygon'] = $this->getPolygon();
        }
        if (in_array('coordinates', $include, true)) {
            $data['coordinates'] = $this->getCoordinates();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getGroupsArray();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }
        if (in_array('color', $include, true)) {
            $data['color'] = $this->getColor();
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
     * @var string|null
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'areas')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'polygon', type: 'geometry')]
    private $polygon;

    /**
     * @var string
     */
    #[ORM\Column(name: 'coordinates', type: 'json')]
    private $coordinates;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100)]
    private $status;

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
     * Many Area have Many Groups.
     */
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
    #[ORM\ManyToMany(targetEntity: 'AreaGroup', mappedBy: 'areas', fetch: 'EXTRA_LAZY')]
    private $groups;

    /**
     * Many Areas have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'areas', fetch: 'EXTRA_LAZY')]
    private $userGroups;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 100, options: ['default' => self::TYPE_CUSTOM])]
    private $type;

    /**
     * @var string
     */
    #[ORM\Column(name: 'external_id', type: 'integer', nullable: true)]
    private $externalId;

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
     * @param string|null $name
     *
     * @return Area
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get coordinates.
     *
     * @return string
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Set coordinates.
     *
     * @param string $coordinates
     *
     * @return Area
     */
    public function setCoordinates($coordinates)
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
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
     * @return Area
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
     * Set polygon.
     *
     * @param string $polygon
     *
     * @return Area
     */
    public function setPolygon($polygon)
    {
        $this->polygon = "POLYGON(($polygon))";

        return $this;
    }

    /**
     * Get polygon.
     *
     * @return string
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Area
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Area
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
     * @return Area
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
     * @return Area
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
     * @return Area
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
     * @return array|null
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()->getId();
    }

    /**
     * @return int|null
     */
    public function getAreaClientId()
    {
        return $this->getTeam()->getClientId();
    }

    /**
     * @param AreaGroup $areaGroup
     */
    public function addToGroup(AreaGroup $areaGroup)
    {
        $this->groups->add($areaGroup);
    }

    /**
     * @param AreaGroup $areaGroup
     */
    public function removeFromGroup(AreaGroup $areaGroup)
    {
        $this->groups->removeElement($areaGroup);
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getGroupsArray(): array
    {
        return $this->getGroups()->map(
            static function (AreaGroup $g) {
                return $g->toArray(['name', 'color']);
            }
        )->toArray();
    }

    /**
     * @return |null
     */
    public function getColor()
    {
        $color = null;

        foreach ($this->getGroups() as $group) {
            $color = $group->getColor();
        }

        return $color;
    }

    /**
     * @param UserGroup $userGroup
     */
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

    public function getGroupsId()
    {
        return $this->getGroups()->map(
            static function (AreaGroup $g) {
                return $g->getId();
            }
        )->toArray();
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

    public function setExternalId(int $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getExternalId(): int
    {
        return $this->externalId;
    }

    public function getIsReadOnly(): bool
    {
        return in_array($this->getType(), [self::TYPE_FUEL_STATION]);
    }
}
