<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ScopeType
 */
#[ORM\Table(name: 'notification_scope_type')]
#[ORM\UniqueConstraint(columns: ['type', 'sub_type', 'category'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\ScopeTypeRepository')]
class ScopeType extends BaseEntity
{
    public const GENERAL_SCOPE_CATEGORY = 'general';
    public const ADDITIONAL_SCOPE_CATEGORY = 'additional';

    public const USER = 'user';
    public const ADMIN_USER = 'admin_user';
    public const VEHICLE = 'vehicle';
    public const TEAM = 'team';
    public const DEVICE = 'device';
    public const TRACKER_HISTORY = 'trackerhistory';
    public const TRACKER_HISTORY_SENSOR = 'trackerhistorysensor';
    public const AREA = 'area';
    public const AREA_HISTORY = 'areahistory';
    public const DOCUMENT = 'document';
    public const DOCUMENT_RECORD = 'documentrecord';
    public const REMINDER = 'reminder';
    public const SERVICE_RECORD = 'servicerecord';
    public const PANIC_BUTTON = 'panic_button';
    public const ASSET = 'asset';
    public const ANY = 'any';

    public const SUBTYPE_ANY = 'any';
    public const SUBTYPE_USER = 'user';
    public const SUBTYPE_USER_GROUPS = 'user_groups';
    public const SUBTYPE_USER_GROUPS_NAME = 'user groups';
    public const SUBTYPE_TEAM_TYPE = 'team_type';
    public const SUBTYPE_TEAM_TYPE_NAME = 'team type';
    public const SUBTYPE_TEAM = 'team';
    public const SUBTYPE_ROLE = 'role';
    public const SUBTYPE_VEHICLE = 'vehicle';
    public const SUBTYPE_DEPOT = 'depot';
    public const SUBTYPE_GROUP = 'group';
    public const SUBTYPE_DEVICE = 'device';
    public const SUBTYPE_ASSET = 'asset';
    public const SUBTYPE_SENSOR = 'sensor';
    public const SUBTYPE_AREA = 'area';
    public const SUBTYPE_AREAS_GROUP = 'area_group';
    public const SUBTYPE_AREAS_GROUP_NAME = 'area group';
    public const SUBTYPE_DRIVER = 'driver';

    public const SCOPE_TO_SUBTYPES = [
        self::GENERAL_SCOPE_CATEGORY => [
            self::USER => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_USER,
                self::SUBTYPE_ROLE,
                self::SUBTYPE_TEAM,
                self::SUBTYPE_USER_GROUPS,
            ],
            self::ADMIN_USER => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_USER,
                self::SUBTYPE_ROLE,
                self::SUBTYPE_TEAM,
            ],
            self::VEHICLE => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
                self::SUBTYPE_TEAM,
            ],
            self::TEAM => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_TEAM,
                self::SUBTYPE_TEAM_TYPE,
            ],
            self::DEVICE => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_DEVICE,
            ],
            self::ASSET => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_ASSET,
            ],
            self::TRACKER_HISTORY => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
            ],
            self::AREA_HISTORY => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
            ],
            self::DOCUMENT => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
                self::SUBTYPE_DRIVER,
            ],
            self::DOCUMENT_RECORD => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
                self::SUBTYPE_DRIVER,
            ],
            self::REMINDER => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
                self::SUBTYPE_DRIVER,
            ],
            self::SERVICE_RECORD => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
                self::SUBTYPE_DRIVER,
            ],
            self::PANIC_BUTTON => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_VEHICLE,
                self::SUBTYPE_DEPOT,
                self::SUBTYPE_GROUP,
            ],
            self::ANY => [
                self::ANY
            ]
        ],
        self::ADDITIONAL_SCOPE_CATEGORY => [
            self::AREA_HISTORY => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_AREA,
                self::SUBTYPE_AREAS_GROUP,
            ],
            self::AREA => [
                self::SUBTYPE_ANY,
                self::SUBTYPE_AREA,
                self::SUBTYPE_AREAS_GROUP,
            ],
        ],

    ];

    public const SUBTYPES_WITHOUT_VALUE = [
        self::SUBTYPE_ANY,
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'type',
        'subType',
        'category',
    ];

    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }

        if (in_array('subType', $include, true)) {
            $data['subType'] = $this->getSubType();
        }

        if (in_array('category', $include, true)) {
            $data['category'] = $this->getCategory();
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
    #[ORM\Column(name: 'category', type: 'string', length: 255, options: ['default' => 'general'])]
    private $category;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sub_type', type: 'string', length: 255)]
    private $subType;

    /**
     * @var NotificationScopes
     */
    #[ORM\OneToMany(targetEntity: 'NotificationScopes', mappedBy: 'type', fetch: 'EXTRA_LAZY')]
    private $notificationScopes;


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
     * @return ScopeType
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubType(): string
    {
        return $this->subType;
    }

    /**
     * @param string $subType
     * @return $this
     */
    public function setSubType(string $subType)
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory(string $category)
    {
        $this->category = $category;

        return $this;
    }

}
