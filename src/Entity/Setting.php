<?php

namespace App\Entity;

use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 */
#[ORM\Table(name: 'setting')]
#[ORM\Index(name: 'setting_name_team_id_index', columns: ['name', 'team_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\SettingRepository')]
#[ORM\EntityListeners(['App\EventListener\Setting\SettingEntityListener'])]
class Setting extends BaseEntity
{
    use AttributesTrait;

    public const OTP_SETTING = 'otp';
    public const THEME_SETTING = 'theme';
    public const TIMEZONE_SETTING = 'timezone';
    public const SMS_SETTING = 'sms';
    public const EMAIL_SETTING = 'email';
    public const IN_APP_SETTING = 'inApp';
    public const LANGUAGE_SETTING = 'language';
    public const LANGUAGE_SETTING_DEFAULT_VALUE = 'en';
    public const LANGUAGE_LIST = [
        ['value' => 'en', 'label' => 'English'],
        ['value' => 'th', 'label' => 'Thai'],
        ['value' => 'fil', 'label' => 'Filipino'],
        ['value' => 'zh_CN', 'label' => 'Chinese Simplified']
    ];
    public const NOTIFICATION_TEMPLATE_SETTING = 'notificationTemplate';
    public const END_TRIP = 'endTrip';
    public const END_TRIP_VALUE = ['enable' => false, 'value' => 600];
    public const IGNORE_STOPS = 'ignoreStops';
    public const IGNORE_STOPS_VALUE = ['enable' => true, 'value' => 30];
    public const IGNORE_MOVEMENT = 'ignoreMovement';
    public const IGNORE_MOVEMENT_VALUE = ['enable' => true, 'value' => 50];
    public const IDLING = 'idling';
    public const IDLING_VALUE = ['enable' => true, 'value' => 300];
    public const GPS_STATUS_DURATION = 'gpsStatusDuration';
    public const GPS_STATUS_DURATION_VALUE = ['enable' => true, 'value' => 14400];
    public const ROUTES = 'routes';
    public const GEOLOCATION_ROUTE_STOP = 'geolocationRouteStop';
    public const GEOLOCATION_FUEL_STATION = 'geolocationFuelStation';
    public const ECO_SPEED = 'ecoSpeed';
    /** Value in km/h */
    public const ECO_SPEED_VALUE = ['value' => 85];
    public const EXCESSIVE_IDLING = 'excessiveIdling';
    /** Value in seconds */
    public const EXCESSIVE_IDLING_VALUE = ['value' => 120];
    public const MAP_API_OPTIONS = 'mapApiOptions';
    public const MAP_PROVIDER = 'provider';
    public const MAP_PROVIDER_DEFAULT = 'default';
    public const MAP_PROVIDER_GOOGLE_MAPS = 'googleMaps';
    public const MAP_PROVIDER_MAPBOX = 'mapbox';
    public const MAP_PROVIDER_DEFAULT_VALUE = 3;
    public const VEHICLE_ENGINE_OFF = 'vehicleEngineOff';
    public const VEHICLE_ENGINE_OFF_VALUE = ['enable' => false, 'value' => 300];
    public const DEVICE_VOLTAGE = 'deviceVoltage';
    public const DEVICE_VOLTAGE_LIMIT = 3700;
    public const DEVICE_VOLTAGE_VALUE = ['value' => self::DEVICE_VOLTAGE_LIMIT];
    public const INSPECTION_FORM_PERIOD = 'inspectionFormPeriod';
    public const INSPECTION_FORM_PERIOD_EVERY_TIME = 'inspectionFormPeriodEveryTime';
    public const INSPECTION_FORM_PERIOD_ONCE_PER_DAY = 'inspectionFormPeriodOncePerDay';
    public const INSPECTION_FORM_PERIOD_NEVER = 'inspectionFormPeriodNever';
    public const OVERSPEEDING_DURATION = 'overSpeedingDuration';
    public const OVERSPEEDING_DURATION_VALUE = ['value' => 120];
    public const LONG_STANDING_DURATION = 'longStandingDuration';
    public const LONG_STANDING_DURATION_VALUE = ['value' => 600];
    public const LONG_DRIVING_DURATION = 'longDrivingDuration';
    public const LONG_DRIVING_DURATION_VALUE = ['value' => 18000]; //seconds
    public const LOGIN_WITH_ID = 'loginWithId';
    public const TRACKING_LINK = 'trackingLink';
    public const TRACKING_LINK_VALUE = false;
    public const TRACKING_LINK_DEFAULT_MESSAGE = 'trackingLinkDefaultMessage';
    public const TRACKING_LINK_DEFAULT_MESSAGE_VALUE = '';
    public const INTEGRATIONS = 'integrations';
    public const INTEGRATIONS_DEFAULT_VALUE = [1, 2, 3, 4, 5];
    public const NOTIFICATION_POPUP = 'notificationPopup';
    public const NOTIFICATION_POPUP_VALUE = true;
    public const NOTIFICATION_SOUND = 'notificationSound';
    public const NOTIFICATION_SOUND_VALUE = true;
    public const DIGITAL_FORM = 'digitalForm';
    public const DIGITAL_FORM_DEFAULT_VALUE = false;
    public const DRIVING_BEHAVIOR_CALCULATION_TYPE = 'drivingBehaviorCalculationType';
    public const DRIVING_BEHAVIOR_CALCULATION_TYPE_DEFAULT_VALUE = DrivingBehaviorService::CALCULATION_TYPE_ROUTE;
    public const HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP = 'hideFormsOnTheDashboardsInMobileApp';
    public const HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP_DEFAULT_VALUE = false;
    public const ASSET_SETTING = 'asset';
    public const DISALLOW_DRIVER_LOGIN_WEBAPP = 'disallowDriverLoginWebapp';
    public const DISALLOW_DRIVER_LOGIN_WEBAPP_VALUE = false;
    public const DATE_FORMAT = 'dateFormat';
    public const DATE_FORMAT_VALUE = 'DD/MM/YYYY';
    public const DATE_FORMAT_VALUES = [
        'DD/MM/YYYY' => 'd/m/Y',
        'MM/DD/YYYY' => 'm/d/Y',
        'YYYY/MM/DD' => 'Y/m/d',
        'YYYY-MM-DD' => 'Y-m-d'
    ];

    public const VEHICLE_MAP_TITLE_INSTEAD_OF_REGNO = 'vehicleMapTitleInsteadOfRegno';
    public const VEHICLE_MAP_TITLE_INSTEAD_OF_REGNO_VALUE = false;
    public const VEHICLE_MAP_FIELD_WITH_ICON = 'vehicleMapFieldWithIcon';
    public const VEHICLE_MAP_FIELD_WITH_ICON_VALUE = 'titleAndRegno';
    public const MESSENGER = 'messenger';
    public const MESSENGER_DEFAULT_VALUE = false;
    public const MESSENGER_UPLOAD_FILE_LIMIT = 'messengerUploadFileLimit';
    public const MESSENGER_UPLOAD_FILE_LIMIT_DEFAULT_VALUE = ['value' => 1000 * 1000 * 10]; // bytes
    public const BILLING = 'billing';
    public const BILLING_VALUE = false;
    public const REPORTS = 'reports';
    public const REPORTS_DEFAULT_VALUE = [];
    public const DRIVER_AUTO_LOGOUT_OPTION = 'driverAutoLogoutOption';
    public const DRIVER_AUTO_LOGOUT_OPTION_VALUE = false;
    public const DRIVER_AUTO_LOGOUT_BY_APP = 'driverAutoLogoutByApp';
    public const DRIVER_AUTO_LOGOUT_BY_APP_VALUE = ['enable' => false, 'value' => 0];
    public const DRIVER_AUTO_LOGOUT_BY_VEHICLE = 'driverAutoLogoutByVehicle';
    public const DRIVER_AUTO_LOGOUT_BY_VEHICLE_VALUE = ['enable' => false, 'value' => 0];
    public const ROUTE_OPTIMIZATION = 'routeOptimization';
    public const ROUTE_OPTIMIZATION_VALUE = false;
    public const ROUTE_SCOPE = 'routeScope';
    public const ROUTE_SCOPE_DEFAULT_VALUE = ['value' => Route::SCOPE_UNCATEGORISED];
    public const TIME_12H_VALUE = false;
    public const TIME_12H = 'time12h';
    public const USER_AUTO_LOGOUT_OPTION = 'userAutoLogoutOption';
    public const USER_AUTO_LOGOUT_OPTION_VALUE = ['enable' => false, 'value' => 3600];
    public const USER_LOCAL_AUTH_OPTION = 'userLocalAuthOption';
    public const USER_LOCAL_AUTH_OPTION_VALUE = ['enable' => false, 'value' => null];
    public const USER_TERMS_ACCEPTANCE_VALUE = false;
    public const USER_TERMS_ACCEPTANCE = 'userTermsAcceptance';
    public const TRIP_CODE_VALUE = false;
    public const TRIP_CODE = 'tripCode';
    public const BILLABLE_ADDONS = 'billableAddons';
    public const BILLABLE_ADDONS_VALUE = [];
    public const BILLABLE_ADDONS_SIGN_POST_SPEED_DATA = 'signPostSpeedData';
    public const CAMERAS = 'cameras';
    public const CAMERAS_VALUE = false;
    public const BIOMETRIC_LOGIN_VALUE = false;
    public const BIOMETRIC_LOGIN = 'biometricLogin';
    public const DEVICE_TTS = 'deviceTts';
    public const BILLABLE_ADDONS_SNAP_TO_ROADS = 'snapToRoads';
    public const DRIVER_VEHICLES_ACCESS_TYPE = 'driverVehiclesAccessType';
    public const DRIVER_VEHICLES_ACCESS_TYPE_DEFAULT_VALUE = self::DRIVER_VEHICLES_ACCESS_TYPE_ALL_DRIVEN;
    public const DRIVER_VEHICLES_ACCESS_TYPE_ALL_DRIVEN = 'allDriven';
    public const DRIVER_VEHICLES_ACCESS_TYPE_ALL = 'all';
    public const DRIVER_VEHICLES_ACCESS_TYPE_CURRENT = 'current';

    public const ALLOWED_TRANSPORT_SETTINGS = [
        self::SMS_SETTING,
        self::EMAIL_SETTING,
        self::IN_APP_SETTING,
    ];

    public const MAP_PROVIDER_IDS = [
        1 => self::MAP_PROVIDER_DEFAULT,
        2 => self::MAP_PROVIDER_GOOGLE_MAPS,
        3 => self::MAP_PROVIDER_MAPBOX
    ];

    public const MAP_API_OPTIONS_IDS = [
        1 => self::ROUTES,
        2 => self::GEOLOCATION_ROUTE_STOP,
        3 => self::GEOLOCATION_FUEL_STATION
    ];

    public const ENABLED = 1;
    public const DISABLED = 0;

    public const DEFAULT_DISPLAY_VALUES = [
        'role',
        'team',
        'user',
        'name',
        'value'
    ];

    public const SIMPLE_VALUES = [
        'role',
        'user',
        'name',
        'value'
    ];

    /**
     * Setting constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->role = $fields['role'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->name = $fields['name'];
        $this->user = $fields['user'] ?? null;
        $this->value = $fields['value'];
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('role', $include, true)) {
            $data['role'] = $this->role ? $this->getRole()->toArray(Role::SIMPLE_DISPLAY_VALUES) : null;
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser() ? $this->getUser()->toArray(User::SIMPLE_VALUES) : null;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('value', $include, true)) {
            $data['value'] = $this->getValue();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Role')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: true)]
    private $role;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private $name;

    /**
     * @var int
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: true)]
    private $value;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;


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
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->getTeam()->getClient();
    }

    /**
     * Set role
     *
     * @param Role $role
     *
     * @return Setting
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
     * Get role ID
     *
     * @return int|null
     */
    public function getRoleId()
    {
        return $this->getRole() ? $this->getRole()->getId() : null;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return Setting
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
     * Set name
     *
     * @param string $name
     *
     * @return Setting
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
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}

