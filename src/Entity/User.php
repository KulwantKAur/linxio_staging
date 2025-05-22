<?php

namespace App\Entity;

use App\Entity\Notification\Event;
use App\Entity\Notification\Message;
use App\Service\File\LocalFileService;
use App\Service\User\UserService;
use App\Util\AttributesTrait;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @UniqueEntity(
 *     groups={"Default", "create", "edit"},
 *     fields={"driverSensorId"},
 *     message="Driver BLE tag with this value already exists."
 * )
 * @UniqueEntity(
 *     groups={"Default", "create", "edit"},
 *     fields={"driverFOBId", "team"},
 *     errorPath="driverFOBId",
 *     message="Driver FOB ID with this value already exists in team."
 * )
 * @UniqueEntity(
 *     groups={"Default", "create", "sso", "edit"},
 *     fields={"email"}
 * )
 */

#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'users_network_status_last_online_date_index', columns: ['network_status', 'last_online_date'])]
#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners(['App\EventListener\User\UserEntityListener'])]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    use AttributesTrait;

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_BLOCKED,
        self::STATUS_BLOCKED_OVERDUE,
        self::STATUS_DELETED,
        self::STATUS_NEW
    ];
    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;
    public const STATUS_NEW = 'new';
    public const ME = 'me';
    public const NETWORK_STATUS_ONLINE = 1;
    public const NETWORK_STATUS_OFFLINE = 0;
    public $cache = [];

    public const LIST_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_ACTIVE,
        self::STATUS_BLOCKED,
        self::STATUS_BLOCKED_OVERDUE
    ];

    public const LIST_NETWORK_STATUSES = [
        self::NETWORK_STATUS_ONLINE,
        self::NETWORK_STATUS_OFFLINE,
    ];

    public const STATUS_ONLINE_DURATION = 120; // sec

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'email',
        'name',
        'surname',
        'team',
        'teamType',
        'roleId',
        'role',
        'position',
        'phone',
        'status',
        'picture',
        'createdAt',
        'createdById',
        'createdByName',
        'updatedAt',
        'lastLoggedAt',
        'is2faEnabled',
        'driverId',
        'blockingMessage',
        'isPhoneVerified',
        'managedTeams',
        'timezone',
        'userGroups',
        'driverSensorId',
        'driverFOBId',
        'language',
        'driverRouteScope',
        'isDualAccount',
        'isSSO',
        'driverRouteComment'
    ];

    public const DISPLAYED_VALUES = [
        'id',
        'email',
        'name',
        'surname',
        'roleData',
        'phone',
        'status',
        'lastLoggedAt',
        'driverSensorId',
        'driverFOBId',
        'isDualAccount',
    ];

    public const DRIVERS_LIST_FIELDS = [
        'id',
        'email',
        'name',
        'surname',
        'roleData',
        'phone',
        'status',
        'lastLoggedAt',
        'driverSensorId',
        'driverFOBId',
        'driverRouteScope',
        'isDualAccount',
    ];

    public const CLIENT_LIST_DISPLAYED_VALUES = [
        'id',
        'email',
        'name',
        'surname',
        'roleData'
    ];

    public const SIMPLE_VALUES = [
        'id',
        'email',
        'name',
        'surname',
        'roleData',
        'phone',
        'status',
        'lastLoggedAt',
        'driverSensorId',
        'driverFOBId',
    ];

    public const SIMPLE_VALUES_CHAT = [
        'id',
        'email',
        'name',
        'surname',
        'fullName',
        'lastLoggedAt',
        'networkStatus',
        'picture',
        'role',
        'phone',
        'position',
    ];

    public const CREATED_BY_FIELDS = [
        'id',
        'name',
        'surname',
        'fullName'
    ];

    public const EDITABLE_FIELDS_BY_ROLE = [
        Team::TEAM_CLIENT => [
            Role::ROLE_CLIENT_ADMIN => [
                'avatar',
                'name',
                'surname',
                'phone',
                'driverId',
                'roleId',
                'position',
                'status',
                'isBlocked',
                'blockingMessage',
                'groups',
                'driverSensorId',
                'driverFOBId',
                'driverRouteScope',
                'language',
                'isDualAccount',
                'isSSO',
                'timezone',
                'email',
                'driverRouteComment'
            ],
            Role::ROLE_MANAGER => [
                'avatar',
                'phone',
                'driverId',
                'roleId',
                'position',
                'groups',
                'driverSensorId',
                'driverFOBId',
                'driverRouteScope',
                'language',
                'isDualAccount',
                'timezone',
                'driverRouteComment'
            ],
            User::ME => [
                'avatar',
                'phone',
                'driverId',
                'position',
                'language',
                'driverRouteScope',
                'timezone',
                'driverRouteComment'
            ],
        ],
        Team::TEAM_ADMIN => [
            'email',
            'name',
            'surname',
            'roleId',
            'driverId',
            'blockingMessage',
            'position',
            'phone',
            'allTeamsPermissions',
            'isBlocked',
            'avatar',
            'status',
            'driverSensorId',
            'driverFOBId',
            'driverRouteScope',
            'language',
            'groups',
            'isDualAccount',
            'isSSO',
            'timezone',
            'driverRouteComment'
        ],
        Team::TEAM_RESELLER => [
            'email',
            'name',
            'surname',
            'roleId',
            'driverId',
            'blockingMessage',
            'position',
            'phone',
            'allTeamsPermissions',
            'isBlocked',
            'avatar',
            'status',
            'groups',
            'driverSensorId',
            'driverFOBId',
            'driverRouteScope',
            'language',
            'isDualAccount',
            'timezone',
            'driverRouteComment'
        ]
    ];

    public function __toString()
    {
        return $this->getId() ? strval($this->getId()) : '';
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

        if (in_array('fullName', $include, true)) {
            $data['fullName'] = $this->getFullName();
        }

        if (in_array('email', $include, true)) {
            $data['email'] = $this->email;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('surname', $include, true)) {
            $data['surname'] = $this->surname;
        }

        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->team->getId();
        }

        if (in_array('role', $include, true)) {
            $data['role'] = $this->getRole() ? $this->getRole()->toArray() : null;
        }

        if (in_array('roleData', $include, true)) {
            $data['role'] = $this->getRole()->toArray(['id', 'name', 'displayName']);
        }

        if (in_array('roleDisplayName', $include, true)) {
            $data['roleDisplayName'] = $this->getRoleDisplayName();
        }

        if (in_array('roleId', $include, true)) {
            $data['roleId'] = $this->getRole() ? $this->getRole()->getId() : null;
        }

        if (in_array('position', $include, true)) {
            $data['position'] = $this->position;
        }

        if (in_array('phone', $include, true)) {
            $data['phone'] = $this->phone;
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->status;
        }

        if (in_array('picture', $include, true)) {
            $data['picture'] = $this->getPicturePath();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }

        if (in_array('createdById', $include, true)) {
            $data['createdById'] = $this->getCreatedById();
        }

        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->updatedAt);
        }

        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }

        if (in_array('createdByName', $include, true)) {
            $data['createdByName'] = $this->getCreatedByName();
        }

        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->getCreatedBy()->getTeam()->toArray();
        }

        if (in_array('lastLoggedAt', $include, true)) {
            $data['lastLoggedAt'] = $this->formatDate($this->lastLoggedAt);
        }

        if (in_array('lastLoggedAtFormatted', $include, true)) {
            $data['lastLoggedAtFormatted'] = $this->getLastLoggedAtFormatted();
        }

        if (in_array('is2faEnabled', $include, true)) {
            $data['is2faEnabled'] = $this->is2FAEnabled();
        }

        if (in_array('isBlocked', $include, true)) {
            $data['isBlocked'] = $this->isBlocked();
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray() ?? null;
        }
        $data = $this->getNestedFields('team', $include, $data);

        if (in_array('blockingMessage', $include, true)) {
            $data['blockingMessage'] = $this->blockingMessage;
        }

        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->driverId;
        }

        if (in_array('teamPermission', $include, true)) {
            $data['teamPermission'] = $this->getTeamPermissionsArray();
        }

        if (in_array('allTeamsPermissions', $include, true)) {
            $data['allTeamsPermissions'] = $this->isAllTeamsPermissions();
        }

        if (in_array('managedTeams', $include, true)) {
            $data['managedTeams'] = $this->getManagedTeamsArray();
        }

        if (in_array('isPhoneVerified', $include, true)) {
            $data['isPhoneVerified'] = $this->isPhoneVerified();
        }

        if (in_array('teamType', $include, true)) {
            $data['teamType'] = $this->getTeamType();
        }

        if (in_array('timezone', $include, true)) {
            $data['timezone'] = $this->getTimezoneData();
        }

        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicleData();
        }

//        if ($this->isDriverClientOrDualAccount()) {
//            $data['lastRoute'] = $this->getLastRoute();
//            $data['todayData'] = $this->getTodayData();
//        }

        if ($this->isDriverClientOrDualAccount() && in_array('lastRoute', $include, true)) {
            $data['lastRoute'] = $this->getLastRoute();
        }

        if ($this->isDriverClientOrDualAccount() && in_array('todayData', $include, true)) {
            $data['todayData'] = $this->getTodayData();
        }

        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDeviceData();
        }

        if (in_array('permissions', $include, true)) {
            $data['permissions'] = $this->getPermissions();
        }

        if (in_array('userGroups', $include, true)) {
            $data['userGroups'] = $this->getGroupsArray();
        }

        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle() ? $this->getVehicle()->getId() : null;
        }

        if (in_array(Setting::DIGITAL_FORM, $include, true)) {
            /** @var Setting */
            $digitalFormSetting = $this->getSettingByName(Setting::DIGITAL_FORM);
            $data[Setting::DIGITAL_FORM] = ($digitalFormSetting !== null) ? $digitalFormSetting->getValue() : Setting::DIGITAL_FORM_DEFAULT_VALUE;
        }

        if (in_array(Setting::DATE_FORMAT, $include, true)) {
            $data[Setting::DATE_FORMAT] = $this->getDateFormatSetting();
        }

        if (in_array(Setting::USER_TERMS_ACCEPTANCE, $include, true)) {
            $data[Setting::USER_TERMS_ACCEPTANCE] = $this->getUserTermsSetting();
        }

        if ($this->isDriverClientOrDualAccount() || in_array('driverSensorId', $include, true)) {
            $data['driverSensorId'] = $this->driverSensorId;
        }

        if ($this->isDriverClientOrDualAccount() || in_array('driverFOBId', $include, true)) {
            $data['driverFOBId'] = $this->getDriverFOBId();
        }

        if (in_array('plan', $include, true)) {
            $data['plan'] = $this->getPlan() ? $this->getPlan()->toArray([], $this->team) : null;
        }

        if (in_array('keyContactId', $include, true)) {
            $data['keyContactId'] = $this->isInClientTeam() && $this->getClient()->getKeyContact()
                ? (int)$this->getClient()->getKeyContact()->getId() : null;
        }

        if (in_array('language', $include, true)) {
            $data['language'] = $this->getLanguage();
        }

        if (in_array('networkStatus', $include, true)) {
            $data['networkStatus'] = $this->getNetworkStatus();
        }

        if (in_array('lastOnlineDate', $include, true)) {
            $data['lastOnlineDate'] = $this->getLastOnlineDate();
        }

        if (in_array('billingPlanId', $include, true)) {
            $data['billingPlanId'] = $this->getClient()?->getBillingPlan()?->getId();
        }

        if (in_array('resellerId', $include, true)) {
            $data['resellerId'] = $this->getClient()?->getReseller()?->getId();
        }

        if (in_array('driverRouteScope', $include, true)) {
            $data['driverRouteScope'] = $this->getDriverRouteScope();
        }

        if (in_array('isDualAccount', $include, true)) {
            $data['isDualAccount'] = $this->isDualAccount();
        }

        if (in_array('isInDriverList', $include, true)) {
            $data['isInDriverList'] = $this->getIsInDriverList();
        }

        if (in_array('isSSO', $include, true)) {
            $data['isSSO'] = $this->isSSO();
        }
        if (in_array('SSOIntegrationData', $include, true)) {
            $data['SSOIntegrationData'] = $this->getSSOIntegrationData()?->toArray();
        }

        if (in_array('billingPlanId', $include, true)) {
            $data['billingPlanId'] = $this->getClient()?->getBillingPlan()?->getId();
        }

        if (in_array('driverRouteComment', $include, true)) {
            $data['driverRouteComment'] = $this->getDriverRouteComment();
        }

        return $data;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toExport(array $include = []): array
    {
        $data = [];

        if (in_array('fullName', $include, true)) {
            $data['fullName'] = $this->getFullName();
        }

        if (in_array('email', $include, true)) {
            $data['email'] = $this->email;
        }

        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->driverId;
        }

        if (in_array('phone', $include, true)) {
            $data['phone'] = $this->phone;
        }

        if (in_array('position', $include, true)) {
            $data['position'] = $this->position;
        }

        if (in_array('role', $include, true)) {
            $data['role'] = $this->getRole()->getDisplayName();
        }

        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->driverId;
        }

        if ($this->isDriverClientOrDualAccount() || in_array('driverSensorId', $include, true)) {
            $data['driverSensorId'] = $this->getDriverSensorId();
        }

        if ($this->isDriverClientOrDualAccount() || in_array('driverFOBId', $include, true)) {
            $data['driverFOBId'] = $this->getDriverFOBId();
        }

        if (in_array('phone', $include, true)) {
            $data['phone'] = $this->phone;
        }

        if (in_array('position', $include, true)) {
            $data['position'] = $this->position;
        }

        if (in_array('last_logged_at', $include, true)) {
            $data['last_logged_at'] = $this->getLastLoggedAtFormatted();
            $data['lastLoggedAt'] = $this->getLastLoggedAtFormatted();
        }

        if (in_array('lastLoggedAt', $include, true)) {
            $data['lastLoggedAt'] = $this->getLastLoggedAtFormatted();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = ucfirst($this->getStatus());
        }

        if (in_array('userGroups', $include, true)) {
            $data['userGroups'] = $this->getGroupsString();
        }

        return $data;
    }

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->email = strtolower($fields['email']);
        $this->name = $fields['name'];
        $this->surname = $fields['surname'] ?? null;
        $this->role = $fields['role'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->position = $fields['position'] ?? null;
        $this->phone = $fields['phone'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_NEW;
        $this->picture = $fields['picture'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->driverId = $fields['driverId'] ?? null;
        $this->teamPermission = new ArrayCollection();
        $this->managedTeams = new ArrayCollection();
        $this->allTeamsPermissions = $fields['allTeamsPermissions'] ?? false;
        $this->userDevices = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->driverSensorId = $fields['driverSensorId'] ?? null;
        $this->driverRouteScope = isset($fields['driverRouteScope']) && $fields['driverRouteScope'] ? $fields['driverRouteScope'] : null;
        $this->isDualAccount = $fields['isDualAccount'] ?? false;
        $this->SSOIntegrationData = $fields['SSOIntegrationData'] ?? null;
        $this->driverFOBId = $fields['driverFOBId'] ?? null;
        $this->driverRouteComment = $fields['driverRouteComment'] ?? null;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      groups={"Default", "create", "sso", "edit"}
     * )
     * @Assert\Email(groups={"sso"})
     * @Assert\NotBlank(groups={"sso"})
     * @Assert\Unique()
     */
    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    private $email;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      groups={"Default", "create", "sso", "edit"}
     * )
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      groups={"Default", "create", "sso", "edit"}
     * )
     */
    #[ORM\Column(name: 'surname', type: 'string', length: 255, nullable: true)]
    private $surname;

    /**
     * @var string
     */
    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: true)]
    private $password;

    /**
     * @var string
     */
    #[ORM\ManyToOne(targetEntity: 'Role')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private $role;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'position', type: 'string', length: 255, nullable: true)]
    private $position;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      groups={"Default", "create", "sso", "edit"}
     * )
     * @Assert\NotBlank(groups={"sso"})
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    private $phone;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      groups={"Default", "create", "sso", "edit"}
     * )
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: true)]
    private $status;

    /**
     * @var int
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'picture_id', referencedColumnName: 'id')]
    private $picture;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int
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
     * @var \DateTime
     */
    #[ORM\Column(name: 'last_logged_at', type: 'datetime', nullable: true)]
    private $lastLoggedAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'blocking_message', type: 'text', nullable: true)]
    private $blockingMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'driver_id', type: 'string', nullable: true)]
    private $driverId;

    /**
     * @var ArrayCollection
     */
    #[ORM\JoinTable(name: 'admin_team_permission')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'team_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'Team')]
    private $teamPermission;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'all_teams_permissions', type: 'boolean', options: ['default' => '1'])]
    private $allTeamsPermissions;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_phone_verified', type: 'boolean', nullable: false, options: ['default' => false])]
    private $isPhoneVerified = false;

    private $managedTeams;

    /**
     * @var string
     */
    #[ORM\Column(name: 'verify_token', type: 'string', nullable: true)]
    private $verifyToken;

    private $is2FAEnabled = false;

    private $timezone = null;

    #[ORM\OneToMany(targetEntity: 'UserDevice', mappedBy: 'user')]
    private $userDevices;

    private $lastRoute;

    private $todayData;

    /**
     * @var Device
     */
    private $device;

    /**
     * @var string
     */
    #[ORM\Column(name: 'refresh_token', type: 'text', nullable: true)]
    private $refreshToken;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'refresh_token_expire_at', type: 'datetime', nullable: true)]
    private $refreshTokenExpireAt;

    private $permissions = [];

    private $canLoginWithId = false;

    /**
     * Many Vehicles have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'users', fetch: 'EXTRA_LAZY')]
    private $groups;

    /**
     * @todo remove since we have `$sensor`
     * @Assert\Regex(pattern="/^([a-fA-F0-9]{2}){3,6}$/", message="Sensor ID is not valid MAC address", groups={"Default", "edit", "create"})
     */
    #[ORM\Column(name: 'driver_sensor_id', type: 'string', unique: true, nullable: true)]
    private ?string $driverSensorId;

    #[ORM\Column(name: 'driver_fob_id', type: 'string', unique: true, nullable: true)]
    private ?string $driverFOBId;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Sensor', inversedBy: 'driver', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'sensor_id', referencedColumnName: 'id', nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?Sensor $sensor;

    /**
     * @var Message
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Notification\Message', mappedBy: 'updateBy', fetch: 'EXTRA_LAZY')]
    private $message;

    private EntityManager $em;

    private $language;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Chat', mappedBy: 'users', fetch: 'EXTRA_LAZY')]
    private $chats;

    #[ORM\OneToMany(targetEntity: 'App\Entity\ChatHistory', mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private $chatHistories;

    #[ORM\OneToMany(targetEntity: 'App\Entity\ChatHistoryUnread', mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private $chatHistoriesUnread;

    /**
     * @var int
     */
    #[ORM\Column(name: 'network_status', type: 'smallint', length: 1, nullable: false, options: ['default' => '0'])]
    private $networkStatus = self::NETWORK_STATUS_OFFLINE;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'last_online_date', type: 'datetime', nullable: true)]
    private $lastOnlineDate;

    /**
     * @var string|null
     *
     * @Assert\Choice(callback={"App\Entity\Route", "getUserScopes"}, groups={"Default", "create", "edit"})
     */
    #[ORM\Column(name: 'driver_route_scope', type: 'text', length: 50, nullable: true)]
    private $driverRouteScope;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_dual_account', type: 'boolean', options: ['default' => false])]
    private $isDualAccount = false;

    /**
     * @var SSOIntegrationData|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\SSOIntegrationData', inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'sso_integration_data_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?SSOIntegrationData $SSOIntegrationData;

    #[ORM\Column(name: 'driver_route_comment', type: 'text', nullable: true)]
    private $driverRouteComment;

    private UserService $userService;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set role
     *
     * @param Role $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get role name
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->role->getName();
    }

    /**
     * Get team
     *
     * @return string
     */
    public function getTeamType()
    {
        return $this->team ? $this->getTeam()->getType() : null;
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
     * @return User
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return User
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
     * Set picture
     *
     * @param File $picture
     *
     * @return User
     */
    public function setPicture(File $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return int
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get picture path
     *
     * @return string
     */
    public function getPicturePath()
    {
        return $this->picture ? LocalFileService::AVATAR_PUBLIC_PATH . $this->picture->getName() : null;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
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
     * @return User
     */
    public function setCreatedBy($createdBy)
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
     * @return User
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
     * @return User
     */
    public function setUpdatedBy(?User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return array
     */
    public function getUpdatedByData(): ?array
    {
        return $this->updatedBy ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * @return string|null
     *
     */
    public function getUpdatedByName(): ?string
    {
        return $this->updatedBy ? $this->updatedBy->getFullName() : null;
    }

    /**
     * Set lastLoggedAt
     *
     * @param \DateTime $lastLoggedAt
     *
     * @return User
     */
    public function setLastLoggedAt($lastLoggedAt)
    {
        $this->lastLoggedAt = $lastLoggedAt;

        return $this;
    }

    /**
     * Get lastLoggedAtFormatted
     *
     * @return string
     */
    public function getLastLoggedAtFormatted()
    {
        return $this->lastLoggedAt ? Carbon::createFromTimestamp($this->lastLoggedAt->getTimestamp())->format(
            self::EXPORT_DATE_FORMAT
        ) : null;
    }

    /**
     * Get lastLoggedAt
     *
     * @return \DateTime
     */
    public function getLastLoggedAt()
    {
        return $this->lastLoggedAt;
    }

    public function getRoles(): array
    {
        return $this->role ? [$this->getRoleName()] : [];
    }

    public function eraseCredentials()
    {
    }

    public function getUsername()
    {
        return $this->name;
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function is2FAEnabled(): bool
    {
        return $this->is2FAEnabled;
    }

    /**
     * @param bool $is2FAEnabled
     */
    public function setIs2FAEnabled(bool $is2FAEnabled)
    {
        $this->is2FAEnabled = $is2FAEnabled;
    }

    public function getFullName()
    {
        return trim($this->name . ' ' . $this->surname);
    }

    /**
     * @return string
     */
    public function getBlockingMessage(): ?string
    {
        return $this->blockingMessage;
    }

    /**
     * @param string $blockingMessage
     */
    public function setBlockingMessage(?string $blockingMessage): void
    {
        $this->blockingMessage = $blockingMessage;
    }

    /**
     * @return string
     */
    public function getDriverId(): ?string
    {
        return $this->driverId;
    }

    /**
     * @param string $driverId
     */
    public function setDriverId(string $driverId): void
    {
        $this->driverId = $driverId;
    }

    public function activate(): void
    {
        $this->setStatus(self::STATUS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return self::STATUS_BLOCKED === $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return self::STATUS_DELETED === $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return $this->isPhoneVerified;
    }

    public function verifyPhone(): void
    {
        $this->isPhoneVerified = true;
    }

    public function unverifyPhone(): void
    {
        $this->isPhoneVerified = false;
    }

    /**
     * @param Team $team
     */
    public function addTeamPermission(Team $team)
    {
        if (!$this->hasTeamPermission($team->getId())) {
            $this->teamPermission->add($team);
        }
    }

    /**
     * @param Team $team
     */
    public function removeTeamPermission(Team $team)
    {
        $this->teamPermission->removeElement($team);
    }

    /**
     * @param $teamId
     * @return bool
     */
    public function hasTeamPermission($teamId)
    {
        return (bool)$this->teamPermission->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('id', $teamId))
        )->count();
    }

    /**
     *
     */
    public function clearTeamPermission()
    {
        $this->teamPermission->clear();
    }

    /**
     * @return array
     */
    public function getTeamPermissions()
    {
        return array_map(
            function ($permission) {
                return $permission;
            },
            $this->teamPermission->toArray()
        );
    }

    public function getTeamPermissionsArray()
    {
        return array_map(
            function ($permission) {
                return $permission->toArray();
            },
            $this->teamPermission->toArray()
        );
    }

    /**
     * @return bool
     */
    public function isAllTeamsPermissions(): bool
    {
        return $this->allTeamsPermissions;
    }

    /**
     * @param bool $allTeamsPermissions
     */
    public function setAllTeamsPermissions(bool $allTeamsPermissions)
    {
        $this->allTeamsPermissions = $allTeamsPermissions;
    }

    /**
     * @return array
     */
    public function getManagedTeamsArray()
    {
        if (!$this->getManagedTeams()) {
            return null;
        }

        return array_map(
            function ($team) {
                return $team->toArray();
            },
            is_array($this->getManagedTeams()) ? $this->getManagedTeams() : $this->getManagedTeams()->toArray()
        );
    }

    /**
     * @return array
     */
    public function getManagedTeamsIds()
    {
        return array_map(
            function ($permission) {
                return $permission->getId();
            },
            array_merge($this->teamPermission->toArray(), $this->getManagedTeams())
        );
    }

    public function setManagedTeams($teams)
    {
        return $this->managedTeams = $teams;
    }

    public function getManagedTeams()
    {
        if (!$this->managedTeams) {
            $this->managedTeams = $this->em->getRepository(Team::class)->findByManager($this);
        }

        return $this->managedTeams;
    }

    /**
     * @return string
     */
    public function getVerifyToken()
    {
        return $this->verifyToken;
    }

    /**
     * @param string $verifyToken
     */
    public function setVerifyToken(string $verifyToken): void
    {
        $this->verifyToken = $verifyToken;
    }

    /**
     * @return bool
     */
    public function isInClientTeam()
    {
        return $this->getTeamType() === Team::TEAM_CLIENT;
    }

    /**
     * @return bool
     */
    public function isInAdminTeam()
    {
        return $this->getTeamType() === Team::TEAM_ADMIN;
    }

    /**
     * @return bool
     */
    public function isInResellerTeam()
    {
        return $this->getTeamType() === Team::TEAM_RESELLER;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->getRoleName() === Role::ROLE_SUPER_ADMIN;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isInAdminTeam() && $this->getRoleName() === Role::ROLE_ADMIN;
    }

    /**
     * @return bool
     */
    public function isClientManager(): bool
    {
        //todo refactor ?
        return $this->getRoleName() === Role::ROLE_SALES_REP || $this->getRoleName() === Role::ROLE_ACCOUNT_MANAGER;
    }

    public function isSalesManager(): bool
    {
        return $this->getRoleName() === Role::ROLE_SALES_REP;
    }

    public function isAccountManager(): bool
    {
        return $this->getRoleName() === Role::ROLE_ACCOUNT_MANAGER;
    }

    /**
     * @return bool
     */
    public function isInstaller()
    {
        return $this->getRoleName() === Role::ROLE_INSTALLER;
    }

    /**
     * @return bool
     */
    public function isSupport()
    {
        return $this->getRoleName() === Role::ROLE_SUPPORT;
    }

    /**
     * @return bool
     */
    public function isAdminClient()
    {
        return $this->isInClientTeam() && $this->getRoleName() === Role::ROLE_CLIENT_ADMIN;
    }

    /**
     * @return bool
     */
    public function isManagerClient()
    {
        return $this->getRoleName() === Role::ROLE_MANAGER;
    }

    /**
     * @return bool
     */
    public function isDriverClient()
    {
        return $this->getRoleName() === Role::ROLE_CLIENT_DRIVER;
    }

    /**
     * @return bool
     */
    public function isDriverClientOrDualAccount()
    {
        return $this->isDriverClient() || $this->isDualAccount();
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->getTeam()->getClientId();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->getTeam()->getClient();
    }

    /**
     * @return int
     */
    public function getClientGpsStatusDuration()
    {
        return $this->getClient() ? $this->getClient()->getGpsStatusDuration() : Client::DEFAULT_GPS_STATUS_DURATION;
    }

    /**
     * @return string|null
     */
    public function getCreatedByName()
    {
        return $this->createdBy ? $this->getCreatedBy()->getFullName() : null;
    }

    /**
     * @return string|null
     */
    public function getRoleDisplayName()
    {
        return $this->role ? $this->getRole()->getDisplayName() : null;
    }

    /**
     * @return int|null
     */
    public function getCreatedById()
    {
        return $this->createdBy ? $this->getCreatedBy()->getId() : null;
    }

    /**
     * Get createdBy
     *
     * @return array
     */
    public function getCreatedByData(): ?array
    {
        return $this->updatedBy ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param TimeZone $timeZone
     */
    public function setTimezone(TimeZone $timeZone)
    {
        $this->timezone = $timeZone;
    }

    /**
     * @return bool
     */
    public function isControlAdmin()
    {
        return $this->isInAdminTeam() && in_array($this->getRole()->getName(), Role::ADMIN_CONTROL_ROLES);
    }

    /**
     * @return |null
     */
    public function getTimezoneData()
    {
        return $this->timezone ? $this->timezone->toArray() : null;
    }

    /**
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->timezone ? $this->timezone->getName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return ArrayCollection
     */
    public function getUserDevices()
    {
        return $this->userDevices;
    }

    /**
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->em->getRepository(Vehicle::class)->findOneBy(['driver' => $this]);
    }

    /**
     * @return array|null
     */
    public function getVehicleData(): ?array
    {
        return $this->getVehicle()
            ? $this->getVehicle()->toArray(array_merge(Vehicle::DISPLAYED_VALUES, ['device', 'groups', 'depot']))
            : null;
    }

    /**
     * @param Route|null $route
     * @return $this
     */
    public function setLastRoute(?Route $route)
    {
        $this->lastRoute = $route;

        return $this;
    }

    /**
     * @return Route|null
     */
    public function getLastRoute(): ?array
    {
        if (!$this->lastRoute) {
            $this->lastRoute = $this->em->getRepository(Route::class)->getDriverLastRoute($this);
        }

        return $this->lastRoute ? $this->lastRoute->toArray() : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setTodayData(array $data)
    {
        $this->todayData = $data;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTodayData(): ?array
    {
        if (!$this->todayData) {
            $this->todayData = $this->userService->getDailyData($this);
        }

        return $this->todayData;
    }

    /**
     * @return Device|null
     */
    public function getDevice()
    {
        return $this->getVehicle() ? $this->getVehicle()->getDevice() : null;
    }

    /**
     * @param Device|null $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceData(): ?array
    {
        return $this->getDevice() ? $this->getDevice()->toArray() : null;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getRefreshToken(): ?string
    {
        return $this->isRefreshTokenExpired() ? null : $this->refreshToken;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setRefreshToken(string $token)
    {
        $this->refreshToken = $token;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getRefreshTokenExpireAt(): ?\DateTime
    {
        return $this->refreshTokenExpireAt;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setRefreshTokenExpireAt($date)
    {
        $this->refreshTokenExpireAt = $date;

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isRefreshTokenExpired()
    {
        return $this->refreshToken && $this->refreshTokenExpireAt
            ? $this->refreshTokenExpireAt < (new \DateTime())
            : true;
    }

    /**
     * @param int $seconds
     */
    public function generateNewRefreshToken($seconds = 2592000)
    {
        $this->refreshToken = StringHelper::generateRandomString();
        $this->setRefreshTokenExpireAt(Carbon::now()->addSeconds($seconds));
    }

    /**
     * @return Plan|null
     */
    public function getPlan()
    {
        return $this->isInClientTeam() ? $this->getClient()->getPlan() : null;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        //doesn't work correctly without array_values
        return array_values(array_map(
            function ($permission) {
                return $permission->toArray();
            },
            $this->permissions ?? []
        ));
    }

    /**
     * @param $permissions
     * @return $this
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission)
    {
        return in_array($permission, array_column($this->getPermissions(), 'name'));
    }

    /**
     * @return bool
     */
    public function canLoginWithId()
    {
        return $this->isInClientTeam() && $this->canLoginWithId ?? false;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCanLoginWithId($value)
    {
        $this->canLoginWithId = $value;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function getGroupsCount()
    {
        return $this->groups->count();
    }

    /**
     * @return bool
     */
    public function allVehiclesAccess()
    {
        return (bool)$this->getGroups()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('scope', UserGroup::SCOPE_ALL))
                ->orderBy(['id' => Criteria::DESC])
        )->count();
    }

    public function allAreasAccess()
    {
        return (bool)$this->getGroups()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('areaScope', UserGroup::SCOPE_ALL))
                ->orderBy(['id' => Criteria::DESC])
        )->count();
    }

    public function hasAreaGroupScope(): bool
    {
        return (bool)$this->getGroups()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('areaScope', UserGroup::SCOPE_AREA_GROUP))
                ->orderBy(['id' => Criteria::DESC])
        )->count();
    }

    public function hasDepotGroupScope(): bool
    {
        return (bool)$this->getGroups()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('scope', UserGroup::SCOPE_DEPOT))
                ->orderBy(['id' => Criteria::DESC])
        )->count();
    }

    /**
     * @return bool
     */
    public function needToCheckUserGroup()
    {
        if (!isset($this->cache[__METHOD__])) {
            $this->cache[__METHOD__] = ($this->getGroupsCount() && (!$this->allVehiclesAccess() || !$this->allAreasAccess()));
        }

        return $this->cache[__METHOD__];
    }

    /**
     * @return array
     */
    public function getGroupsArray(): array
    {
        return array_map(
            function (UserGroup $group) {
                return $group->toArray(['name']);
            },
            $this->getGroups()->toArray()
        );
    }

    /**
     * @return array
     */
    public function getGroupsString()
    {
        $groups = array_map(
            function ($group) {
                return $group->getName();
            },
            $this->groups->toArray()
        );

        return implode(",", $groups);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function addToGroup(UserGroup $userGroup)
    {
        $this->groups->add($userGroup);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function removeFromGroup(UserGroup $userGroup)
    {
        $this->groups->removeElement($userGroup);
    }

    /**
     * @return array
     */
    public function getGroupsId()
    {
        return $this->getGroups()->map(
            static function (UserGroup $g) {
                return $g->getId();
            }
        )->toArray();
    }

    public function getGroupsPermissions()
    {
        $permissions = $this->getGroups()->matching(
            Criteria::create()->where(Criteria::expr()->eq('status', UserGroup::STATUS_ACTIVE))
        )->map(static function (UserGroup $g) {
            return $g->getPermissions();
        })->toArray();

        return array_merge(...$permissions);
    }

    public function getGroupsPermissionsIds(?int $excludeGroupId = null)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', UserGroup::STATUS_ACTIVE));
        if ($excludeGroupId) {
            $criteria = $criteria->andWhere(Criteria::expr()->neq('id', $excludeGroupId));
        }

        $permissions = $this->getGroups()->matching($criteria)->map(static function (UserGroup $g) {
            return $g->getPermissionsIds();
        })->toArray();

        return $permissions ? array_intersect(...$permissions) : [];
    }

    public function removeFromAllGroups()
    {
        foreach ($this->getGroups() as $group) {
            $group->removeUser($this);
        }
    }

    /**
     * @param $settingName
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection|mixed|null
     */
    public function getSettingByName($settingName)
    {
        $name = is_array($settingName) ? $settingName : [$settingName];
        $settings = $this->getTeam()->getSettings()->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->in('name', $name))
                ->andWhere(
                    Criteria::expr()->orX(
                        Criteria::expr()->eq('user', $this),
                        Criteria::expr()->andX(
                            Criteria::expr()->eq('user', $this),
                            Criteria::expr()->eq('role', $this->getRole())
                        ),
                        Criteria::expr()->andX(
                            Criteria::expr()->isNull('user'),
                            Criteria::expr()->eq('role', $this->getRole())
                        ),
                        Criteria::expr()->andX(
                            Criteria::expr()->isNull('user'),
                            Criteria::expr()->isNull('role')
                        )
                    )
                )
                ->orderBy(['id' => Criteria::DESC, 'name' => Criteria::DESC, 'role' => Criteria::DESC])
        );

        if (is_array($settingName)) {
            return $settings;
        }

        return $settings->count() ? $settings->first() : null;
    }

    /**
     * @return string|null
     */
    public function getDriverSensorId(): ?string
    {
        return $this->driverSensorId;
    }

    /**
     * @param string|null $driverSensorId
     */
    public function setDriverSensorId(?string $driverSensorId): void
    {
        $this->driverSensorId = $driverSensorId;
    }

    /**
     * @return array|null
     */
    public function getVehiclesFromUserGroups(): ?array
    {
        $vehicles = new ArrayCollection();
        $userGroups = $this->getGroups();

        if ($userGroups->isEmpty()) {
            $vehicles = $this->getTeam()->getVehicles();
        } else {
            /** @var UserGroup $userGroup */
            foreach ($userGroups as $userGroup) {
                foreach ($userGroup->getVehiclesByScope() as $vehicleByUserGroup) {
                    if (!$vehicles->contains($vehicleByUserGroup)) {
                        $vehicles->add($vehicleByUserGroup);
                    }
                }

                $vehicleGroups = $userGroup->getVehicleGroups();

                /** @var VehicleGroup $vehicleGroup */
                foreach ($vehicleGroups as $vehicleGroup) {
                    foreach ($vehicleGroup->getVehicleEntities() as $vehicleByVehicleGroup) {
                        if (!$vehicles->contains($vehicleByVehicleGroup)) {
                            $vehicles->add($vehicleByVehicleGroup);
                        }
                    }
                }
            }
        }

        return $vehicles->getValues();
    }

    public function isVehicleInUserGroups(Vehicle $vehicle): bool
    {
        $vehicles = $this->getVehiclesFromUserGroups();
        $vehicleIds = array_map(function (Vehicle $vehicle) {
            return $vehicle->getId();
        }, $vehicles);

        return in_array($vehicle->getId(), $vehicleIds);
    }

    /**
     * @return Sensor|null
     */
    public function getSensor(): ?Sensor
    {
        return $this->sensor;
    }

    /**
     * @param Sensor|null $sensor
     * @return self
     */
    public function setSensor(?Sensor $sensor): self
    {
        $this->sensor = $sensor;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->getName() ?? $this->getEmail();
    }

    public function getReseller(): ?Reseller
    {
        return $this->getTeam()->getReseller();
    }

    public function getUserBlockedEvent()
    {
        switch ($this->getTeamType()) {
            case Team::TEAM_CLIENT:
                return Event::USER_BLOCKED;
            case Team::TEAM_RESELLER:
            case Team::TEAM_ADMIN:
                return Event::ADMIN_USER_BLOCKED;
        }

        return null;
    }

    public function getUserChangedNameEvent()
    {
        switch ($this->getTeamType()) {
            case Team::TEAM_CLIENT:
                return Event::USER_CHANGED_NAME;
            case Team::TEAM_RESELLER:
            case Team::TEAM_ADMIN:
                return Event::ADMIN_USER_CHANGED_NAME;
        }

        return null;
    }

    public function getPlatformSettings(): ?PlatformSetting
    {
        if ($this->getTeam()->isAdminTeam() || $this->getTeam()->isResellerTeam()) {
            return $this->getTeam()->getPlatformSetting();
        }
        if ($this->getTeam()->isClientTeam() && $this->getClient()) {
            return $this->getClient()->getTeam()->getPlatformSettingByTeam();
        }

        return null;
    }

    public function getDateFormatSetting()
    {
        $dateTimeSetting = $this->getSettingByName(Setting::DATE_FORMAT);

        return $dateTimeSetting !== null ? $dateTimeSetting->getValue() : Setting::DATE_FORMAT_VALUE;
    }

    public function getTimeFormatSetting()
    {
        $dateTimeSetting = $this->getSettingByName(Setting::TIME_12H);

        return $dateTimeSetting?->getValue() ? 'h:i a' : 'H:i';
    }

    public function getUserTermsSetting()
    {
        $userTermsSetting = $this->getSettingByName(Setting::USER_TERMS_ACCEPTANCE);

        return $userTermsSetting !== null ? $userTermsSetting->getValue() : false;
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

    public function getExcludePermissions()
    {
        $exclude = [];

        $assetSetting = $this->getSettingByName(Setting::ASSET_SETTING);
        if ($assetSetting && !$assetSetting->getValue()) {
            $exclude = Permission::ASSET_PERMISSIONS_ARRAY;
        }

        $messengerSetting = $this->getSettingByName(Setting::MESSENGER);
        if ($messengerSetting && !$messengerSetting->getValue()) {
            $exclude = array_merge($exclude, Permission::MESSENGER_PERMISSIONS_ARRAY);
        }

        $billingSetting = $this->getSettingByName(Setting::BILLING);
        if ($this->isInClientTeam() && $billingSetting && !$billingSetting->getValue()) {
            $exclude = array_merge($exclude, Permission::BILLING_PERMISSIONS_ARRAY);
        }

        $trackLinkSetting = $this->getSettingByName(Setting::TRACKING_LINK);
        if ($trackLinkSetting && !$trackLinkSetting->getValue()) {
            $exclude[] = Permission::TRACK_LINK_CREATE;
        }

        $camerasSetting = $this->getSettingByName(Setting::CAMERAS);
        if (!$camerasSetting?->getValue()) {
            $exclude[] = Permission::CAMERAS;
        }

        if ($this->getClient()?->isBlockedBilling()) {
            $exclude = array_merge($exclude, Permission::UI_MENU_SECTION_PERMISSIONS_ARRAY);
        }

        return $exclude;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getLanguageSetting(): ?Setting
    {
        return $this->getSettingByName(Setting::LANGUAGE_SETTING);
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return int
     */
    public function getNetworkStatus(): int
    {
        return $this->networkStatus;
    }

    /**
     * @param int $networkStatus
     * @return self
     */
    public function setNetworkStatus(int $networkStatus)
    {
        $this->networkStatus = $networkStatus;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastOnlineDate(): ?\DateTime
    {
        return $this->lastOnlineDate;
    }

    /**
     * @param \DateTime|null $lastOnlineDate
     */
    public function setLastOnlineDate(?\DateTime $lastOnlineDate): void
    {
        $this->lastOnlineDate = $lastOnlineDate;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->getLastOnlineDate()
            && Carbon::parse($this->getLastOnlineDate())->addSeconds(self::STATUS_ONLINE_DURATION) > Carbon::now();
    }

    public function setUserService(UserService $userService): self
    {
        $this->userService = $userService;

        return $this;
    }

    public function getTeamIdWithAccess(): array
    {
        if (!$this->isSuperAdmin() && !$this->isAllTeamsPermissions() && $this->isInAdminTeam()) {
            return $this->getManagedTeamsIds() ?? [];
        }
        if ($this->isInAdminTeam()) {
            return $this->em->getRepository(Client::class)->getAdminClientTeams() ?? [];
        }

        if ($this->isInResellerTeam()) {
            return $this->em->getRepository(Client::class)->getResellerClientTeams($this->getReseller()) ?? [];
        }

        return [];
    }

    /**
     * @return string|null
     */
    public function getDriverRouteScope(): ?string
    {
        return $this->driverRouteScope;
    }

    /**
     * @param string|null $driverRouteScope
     */
    public function setDriverRouteScope(?string $driverRouteScope): void
    {
        $this->driverRouteScope = $driverRouteScope;
    }

    /**
     * @return bool
     */
    public function isDualAccount(): bool
    {
        return $this->isDualAccount;
    }

    /**
     * @return bool
     */
    public function getIsDualAccount(): bool
    {
        return $this->isDualAccount();
    }

    /**
     * @param bool $isDualAccount
     */
    public function setIsDualAccount(bool $isDualAccount): void
    {
        $this->isDualAccount = $isDualAccount;
    }

    /**
     * @return bool
     */
    public function isInDriverList(): bool
    {
        return $this->isDriverClient()
            || ($this->isAdminClient() && $this->isDualAccount())
            || ($this->isManagerClient() && $this->isDualAccount());
    }

    /**
     * @return bool
     */
    public function getIsInDriverList(): bool
    {
        return $this->isInDriverList();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __serialize(): array
    {
        return $this->toArray(['id', 'email']);
    }

    /**
     * @return bool
     */
    public function isSSO(): bool
    {
        return boolval($this->getSSOIntegrationData());
    }

    /**
     * @return SSOIntegrationData|null
     */
    public function getSSOIntegrationData(): ?SSOIntegrationData
    {
        return $this->SSOIntegrationData;
    }

    /**
     * @param SSOIntegrationData|null $SSOIntegrationData
     */
    public function setSSOIntegrationData(?SSOIntegrationData $SSOIntegrationData): void
    {
        $this->SSOIntegrationData = $SSOIntegrationData;
    }

    public function getTimezoneText(): ?string
    {
        $offset = $this->getTimezone() ? Carbon::now($this->getTimezone())->getOffsetString() : null;

        return '(UTC' . $offset . ') ' . $this->getTimezone();
    }

    public function getDomain(): ?string
    {
        return $this->getClient()?->getReseller() || $this->isInResellerTeam() ? $this->getTeam()->getDomain() : null;
    }

    public function getHostApp(): ?string
    {
        return $this->getClient()?->getReseller() || $this->isInResellerTeam() ? $this->getTeam()->getHostApp() : null;
    }

    public function getSupportEmail(): ?string
    {
        return $this->getClient()?->getReseller() ? $this->getTeam()->getSupportEmail() : null;
    }

    public function getProductName(): ?string
    {
        return $this->getClient()?->getReseller() ? $this->getTeam()->getProductName() : null;
    }

    public function getDriverFOBId(): ?string
    {
        return $this->driverFOBId;
    }

    public function setDriverFOBId(?string $driverFOBId): void
    {
        $this->driverFOBId = $driverFOBId;
    }

    public function getDriverRouteComment(): ?string
    {
        return $this->driverRouteComment;
    }

    public function setDriverRouteComment(?string $comment): self
    {
        $this->driverRouteComment = $comment;

        return $this;
    }
}
