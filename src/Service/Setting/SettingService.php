<?php

namespace App\Service\Setting;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\Notification\TemplateSet;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Theme;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\DigitalForm\DigitalFormService;
use App\Service\Setting\Factory\SettingMapperFactory;
use App\Service\Setting\Mapper\BaseSettingMapper;
use App\Service\Setting\Mapper\MapApiSettingMapper;
use App\Service\Setting\Mapper\MapProviderSettingMapper;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingService extends BaseService
{
    public const DEFAULT_SETTINGS = [
        [
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED,
            'role' => [Role::ROLE_CLIENT_ADMIN, Team::TEAM_CLIENT]
        ],
        [
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED,
            'role' => [Role::ROLE_MANAGER, Team::TEAM_CLIENT]
        ],
        [
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED,
            'role' => [Role::ROLE_CLIENT_DRIVER, Team::TEAM_CLIENT]
        ],
        ['name' => Setting::THEME_SETTING, 'value' => Theme::DEFAULT_THEME_ALIAS],
        ['name' => Setting::SMS_SETTING, 'value' => Setting::ENABLED],
        ['name' => Setting::EMAIL_SETTING, 'value' => Setting::ENABLED],
        ['name' => Setting::IN_APP_SETTING, 'value' => Setting::ENABLED],
        ['name' => Setting::LANGUAGE_SETTING, 'value' => Setting::LANGUAGE_SETTING_DEFAULT_VALUE],
        ['name' => Setting::ECO_SPEED, 'value' => Setting::ECO_SPEED_VALUE],
        ['name' => Setting::EXCESSIVE_IDLING, 'value' => Setting::EXCESSIVE_IDLING_VALUE],
        ['name' => Setting::VEHICLE_ENGINE_OFF, 'value' => Setting::VEHICLE_ENGINE_OFF_VALUE],
        ['name' => Setting::LOGIN_WITH_ID, 'value' => Setting::DISABLED],
        ['name' => Setting::MAP_PROVIDER, 'value' => Setting::MAP_PROVIDER_DEFAULT_VALUE],
        ['name' => Setting::MAP_API_OPTIONS, 'value' => [2]],
        ['name' => Setting::INTEGRATIONS, 'value' => Setting::INTEGRATIONS_DEFAULT_VALUE],
        ['name' => Setting::DIGITAL_FORM, 'value' => Setting::DIGITAL_FORM_DEFAULT_VALUE],
        [
            'name' => Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE,
            'value' => Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE_DEFAULT_VALUE
        ],
        [
            'name' => Setting::HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP,
            'value' => Setting::HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP_DEFAULT_VALUE
        ],
        ['name' => Setting::ASSET_SETTING, 'value' => Setting::DISABLED],
        ['name' => Setting::DISALLOW_DRIVER_LOGIN_WEBAPP, 'value' => Setting::DISALLOW_DRIVER_LOGIN_WEBAPP_VALUE],
        ['name' => Setting::GPS_STATUS_DURATION, 'value' => Setting::GPS_STATUS_DURATION_VALUE],
        ['name' => Setting::END_TRIP, 'value' => Setting::END_TRIP_VALUE],
        ['name' => Setting::IGNORE_STOPS, 'value' => Setting::IGNORE_STOPS_VALUE],
        ['name' => Setting::IGNORE_MOVEMENT, 'value' => Setting::IGNORE_MOVEMENT_VALUE],
        ['name' => Setting::IDLING, 'value' => Setting::IDLING_VALUE],
        ['name' => Setting::MESSENGER, 'value' => Setting::MESSENGER_DEFAULT_VALUE],
        ['name' => Setting::MESSENGER_UPLOAD_FILE_LIMIT, 'value' => Setting::MESSENGER_UPLOAD_FILE_LIMIT_DEFAULT_VALUE],
        ['name' => Setting::BILLING, 'value' => Setting::BILLING_VALUE],
        ['name' => Setting::TIME_12H, 'value' => Setting::TIME_12H_VALUE],
        ['name' => Setting::USER_AUTO_LOGOUT_OPTION, 'value' => Setting::USER_AUTO_LOGOUT_OPTION_VALUE],
        ['name' => Setting::USER_LOCAL_AUTH_OPTION, 'value' => Setting::USER_LOCAL_AUTH_OPTION_VALUE],
        ['name' => Setting::USER_TERMS_ACCEPTANCE, 'value' => Setting::USER_TERMS_ACCEPTANCE_VALUE],
        ['name' => Setting::TRIP_CODE, 'value' => Setting::TRIP_CODE_VALUE],
        ['name' => Setting::BILLABLE_ADDONS, 'value' => Setting::BILLABLE_ADDONS_VALUE],
        ['name' => Setting::CAMERAS, 'value' => Setting::CAMERAS_VALUE],
        ['name' => Setting::BIOMETRIC_LOGIN, 'value' => Setting::BIOMETRIC_LOGIN_VALUE],
    ];

    public const DEFAULT_USER_SETTINGS = [
        ['name' => Setting::NOTIFICATION_POPUP, 'value' => Setting::NOTIFICATION_POPUP_VALUE],
        ['name' => Setting::NOTIFICATION_SOUND, 'value' => Setting::NOTIFICATION_SOUND_VALUE]
    ];

    private $em;
    private $translator;

    /** @var DigitalFormService */
    private $formService;
    private $cache = [];

    /**
     * @param string $name
     * @return mixed
     */
    private function getDefaultSettingValueByName(string $name)
    {
        switch ($name) {
            case Setting::GPS_STATUS_DURATION:
                return Setting::GPS_STATUS_DURATION_VALUE;
            case Setting::END_TRIP:
                return Setting::END_TRIP_VALUE;
            case Setting::IGNORE_STOPS:
                return Setting::IGNORE_STOPS_VALUE;
            case Setting::IGNORE_MOVEMENT:
                return Setting::IGNORE_MOVEMENT_VALUE;
            case Setting::IDLING:
                return Setting::IDLING_VALUE;
            case Setting::MESSENGER_UPLOAD_FILE_LIMIT:
                return Setting::MESSENGER_UPLOAD_FILE_LIMIT_DEFAULT_VALUE;
            default:
                return null;
        }
    }

    /**
     * SettingService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, TranslatorInterface $translator, DigitalFormService $formService)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->formService = $formService;
    }

    /**
     * @param int $teamId
     * @return array
     */
    public function getTeamSettings(int $teamId): array
    {
        $team = $this->em->getRepository(Team::class)->find($teamId);

        return $this->prepareSettings(
            $this->em->getRepository(Setting::class)->findBy(
                [
                    'team' => $team
                ],
                [
                    'name' => 'ASC',
                    'role' => 'ASC',
                ]
            ), Setting::SIMPLE_VALUES
        );
    }

    /**
     * @param Team $team
     * @param null $name
     * @return array
     */
    public function getTeamSettingByKey(Team $team, $name = null): ?array
    {
        $settingData = $this->prepareSettings(
            $this->em->getRepository(Setting::class)->findBy(
                [
                    'team' => $team,
                    'name' => $name
                ],
                [
                    'name' => 'ASC',
                    'role' => 'ASC',
                ]
            )
        );

        return $settingData[0] ?? null;
    }

    /**
     * @param Team $team
     * @param null $name
     * @return Setting|null
     */
    public function getTeamSettingByKeyAsEntity(Team $team, $name = null): ?Setting
    {
        return $this->em->getRepository(Setting::class)->findOneBy(
            [
                'team' => $team,
                'name' => $name
            ],
            [
                'name' => 'ASC',
                'role' => 'ASC',
            ]
        );
    }

    /**
     * @param Team $team
     * @param string $name
     * @return array|string|null
     */
    public function getTeamSettingValueByKey(Team $team, string $name)
    {
        $commonName = self::getCommonNameOfSetting($name);
        $setting = $this->getTeamSettingByKeyAsEntity($team, $commonName);

        if ($setting) {
            $settingMapper = SettingMapperFactory::make($setting);
            $value = $settingMapper->getMappedValue($name);
        } else {
            $value = $this->getDefaultSettingValueByName($name);
        }

        return $value;
    }

    /**
     * @param mixed $key
     * @param User $currentUser
     * @return mixed
     */
    public function getSettingByKey($key, User $currentUser)
    {
        return $currentUser->getSettingByName($key);
    }

    /**
     * @param array $settings
     * @param array $include
     * @return array
     */
    protected function prepareSettings(array $settings, $include = [])
    {
        return array_map(
            function (Setting $v) use ($include) {
                $arr = $v->toArray($include);
                if (Setting::THEME_SETTING === $arr['name']) {
                    /** @var Theme $theme */
                    $theme = $this->em->getRepository(Theme::class)->find($arr['value']);

                    $arr['value'] = $theme ? $theme->toArray() : [];
                }

                return $arr;
            },
            $settings
        );
    }

    /**
     * @param int $teamId
     * @param array $fields
     * @param User $currentUser
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function setTeamSettings(int $teamId, array $fields, User $currentUser): array
    {
        $team = $this->em->getRepository(Team::class)->find($teamId);
        $this->validateSettingsData($fields, $team, $currentUser);

        $this->setSettingsArray($team, $fields, $currentUser);

        $this->em->flush();

        return $this->prepareSettings(
            $this->em->getRepository(Setting::class)->findBy(
                [
                    'team' => $team,
                ],
                [
                    'name' => 'ASC',
                    'role' => 'ASC',
                ]
            )
        );
    }

    public function setSettingsArray(Team $team, array $fields, User $currentUser)
    {
        foreach ($fields as $settingData) {
            if ($settingData['name'] === Setting::LOGIN_WITH_ID && $currentUser->isInClientTeam()) {
                continue;
            }

            // if we enable digital form functionality - create default form
            if (($settingData['name'] === Setting::DIGITAL_FORM) && ($settingData['value'] !== Setting::DIGITAL_FORM_DEFAULT_VALUE)) {
                $this->formService->createDefaultForm($currentUser, $team);
            }
            if ($settingData['name'] === Setting::BILLING && $settingData['value'] === Setting::BILLING_VALUE
                && in_array($team->getClient()?->getStatus(),
                    [Client::STATUS_PARTIALLY_BLOCKED_BILLING, Client::STATUS_BLOCKED_BILLING])) {
                $team->getClient()->setStatus(Client::STATUS_CLIENT);
            }
            if ($settingData['name'] === Setting::NOTIFICATION_TEMPLATE_SETTING && !isset($settingData['value'])) {
                $settingData['value'] = $this->em->getRepository(TemplateSet::class)
                    ->findOneBy(['name' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME])->getId();
            }

            $role = isset($settingData['roleId']) ? $this->em->getRepository(Role::class)->find(
                $settingData['roleId']
            ) : null;
            $user = isset($settingData['userId']) ? $this->em->getRepository(User::class)->find(
                $settingData['userId']
            ) : null;
            $setting = $this->em->getRepository(Setting::class)->findOneBy(
                [
                    'team' => $team,
                    'role' => $role,
                    'user' => $user,
                    'name' => $settingData['name']
                ]
            );
            $data = [
                'name' => $settingData['name'],
                'value' => $settingData['value'],
                'role' => $role,
                'team' => $team,
                'user' => $user
            ];

            if (!$setting) {
                $setting = new Setting($data);
                $this->em->persist($setting);
            } else {
                $setting->setAttributes($data);
            }

            $this->validateSettingValue($setting);
        }
    }

    public function setTimezoneSetting(int $timezoneId, Team $team = null, Role $role = null, User $user = null)
    {
        $setting = $this->em->getRepository(Setting::class)
            ->getSettingForUpdate(Setting::TIMEZONE_SETTING, $team, $role, $user);
        $data = [
            'name' => Setting::TIMEZONE_SETTING,
            'value' => $timezoneId,
            'role' => $role,
            'team' => $team,
            'user' => $user
        ];
        if (!$setting) {
            $setting = new Setting($data);
            $this->em->persist($setting);
        } else {
            $setting->setAttributes($data);
        }

        $this->em->flush();

        return $setting;
    }

    /**
     * @param array $data
     * @param Team $team
     * @param User $currentUser
     */
    private function validateSettingsData(array $data, Team $team, User $currentUser)
    {
        $errors = [];

        if ($team->isAdminTeam() && !$currentUser->isControlAdmin()) {
            throw new AccessDeniedException();
        }

        foreach ($data as $key => $setting) {
            if (isset($setting['roleId'])) {
                if (null === $role = $this->em->getRepository(Role::class)->find($setting['roleId'])) {
                    $errors[sprintf('%d.roleId', $key)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                }
                /** @var Role $role */
                if ($role && $role->getTeam() !== $team->getType()) {
                    $errors[sprintf('%d.roleId', $key)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                }
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param Team $team
     * @throws \Exception
     */
    public function createDefaultTeamSettings(Team $team)
    {
        foreach (self::DEFAULT_SETTINGS as $dSetting) {
            switch ($dSetting['name']) {
                case Setting::OTP_SETTING:
                case Setting::SMS_SETTING:
                case Setting::MAP_API_OPTIONS:
                case Setting::EMAIL_SETTING:
                case Setting::IN_APP_SETTING:
                case Setting::ECO_SPEED:
                case Setting::EXCESSIVE_IDLING:
                case Setting::LANGUAGE_SETTING:
                case Setting::VEHICLE_ENGINE_OFF:
                case Setting::LOGIN_WITH_ID:
                case Setting::MAP_PROVIDER:
                case Setting::INTEGRATIONS:
                case Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE:
                case Setting::DIGITAL_FORM:
                case Setting::HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP:
                case Setting::ASSET_SETTING:
                case Setting::DATE_FORMAT:
                case Setting::DISALLOW_DRIVER_LOGIN_WEBAPP:
                case Setting::VEHICLE_MAP_TITLE_INSTEAD_OF_REGNO:
                case Setting::VEHICLE_MAP_FIELD_WITH_ICON:
                case Setting::GPS_STATUS_DURATION:
                case Setting::END_TRIP:
                case Setting::IGNORE_STOPS:
                case Setting::IGNORE_MOVEMENT:
                case Setting::IDLING:
                case Setting::MESSENGER:
                case Setting::MESSENGER_UPLOAD_FILE_LIMIT:
                case Setting::BILLING:
                case Setting::REPORTS:
                case Setting::DRIVER_AUTO_LOGOUT_BY_APP:
                case Setting::DRIVER_AUTO_LOGOUT_BY_VEHICLE:
                case Setting::DRIVER_AUTO_LOGOUT_OPTION:
                case Setting::ROUTE_OPTIMIZATION:
                case Setting::ROUTE_SCOPE:
                case Setting::TIME_12H:
                case Setting::BILLABLE_ADDONS:
                case Setting::USER_AUTO_LOGOUT_OPTION:
                case Setting::USER_LOCAL_AUTH_OPTION:
                case Setting::USER_TERMS_ACCEPTANCE:
                case Setting::TRIP_CODE:
                case Setting::CAMERAS:
                case Setting::BIOMETRIC_LOGIN:
                case Setting::DRIVER_VEHICLES_ACCESS_TYPE:
                    $value = $dSetting['value'];
                    break;
                case Setting::THEME_SETTING:
                    $value = $this
                        ->em
                        ->getRepository(Theme::class)
                        ->findOneBy(['alias' => $dSetting['value']])
                        ->getId();
                    break;
                default:
                    throw new \Exception('Invalid setting name');
            }

            $role = null;
            if (isset($dSetting['role']) && !empty($dSetting['role'])) {
                $role = $this->em->getRepository(Role::class)->findOneBy(
                    ['name' => $dSetting['role'][0], 'team' => $dSetting['role'][1]]
                );
            }

            $this->em->persist(
                new Setting(
                    [
                        'name' => $dSetting['name'],
                        'value' => $value,
                        'team' => $team,
                        'role' => $role,
                    ]
                )
            );
        }

        $this->em->flush();
    }

    public function createDefaultUserSettings(User $user): User
    {
        foreach (self::DEFAULT_USER_SETTINGS as $setting) {
            $this->em->persist(
                new Setting(
                    [
                        'name' => $setting['name'],
                        'value' => $setting['value'],
                        'team' => $user->getTeam(),
                        'role' => $user->getRole(),
                        'user' => $user
                    ]
                )
            );
        }

        $this->em->flush();

        return $user;
    }

    /**
     * @param array $settings
     * @param string $name
     * @return array|string|null
     */
    public function getSettingValueFromList(array $settings, string $name)
    {
        $commonName = self::getCommonNameOfSetting($name);
        $key = array_search($commonName, array_column($settings, 'name'));

        if (isset($settings[$key])) {
            $settings[$key] = self::mapSettingByNameAndSubName($name, $settings[$key]);
        }

        return ($key !== false) && isset($settings[$key]) ? $settings[$key]['value'] : null;
    }

    /**
     * @param Device $device
     * @param null $geolocationRouteStopSetting
     * @return bool
     */
    public function isAllowGeolocationForStoppedRoutes(
        Device $device,
        $geolocationRouteStopSetting = null
    ): bool {
        $isAllowGeolocation = false;
        $geolocationRouteStopSetting = $geolocationRouteStopSetting
            ?: $this->getTeamSettingValueByKey($device->getTeam(), Setting::GEOLOCATION_ROUTE_STOP);

        if ($geolocationRouteStopSetting) {
            $isAllowGeolocation = true;
        }

        return $isAllowGeolocation;
    }

    /**
     * @param Vehicle $vehicle
     * @return bool
     */
    public function isAllowGeolocationForFuelStation(Vehicle $vehicle): bool
    {
        $isAllowGeolocation = false;
        $geolocationFuelStationSetting = $this
            ->getTeamSettingValueByKey($vehicle->getTeam(), Setting::GEOLOCATION_FUEL_STATION);

        if ($geolocationFuelStationSetting) {
            $isAllowGeolocation = true;
        }

        return $isAllowGeolocation;
    }

    /**
     * @param Vehicle $vehicle
     * @return bool
     */
    public function isEcoSpeedEnabled(Vehicle $vehicle): bool
    {
        $vehicleEcoSpeed = $vehicle->getEcoSpeed();
        $ecoSpeedSetting = $this->getTeamSettingValueByKey($vehicle->getTeam(), Setting::ECO_SPEED);

        return $vehicleEcoSpeed || $ecoSpeedSetting;
    }

    /**
     * @param Vehicle $vehicle
     * @return float|null
     */
    public function getEcoSpeedValue(Vehicle $vehicle): ?float
    {
        $vehicleEcoSpeed = $vehicle->getEcoSpeed();
        $ecoSpeedSetting = $this->getTeamSettingValueByKey($vehicle->getTeam(), Setting::ECO_SPEED);
        $ecoSpeedSettingValue = $ecoSpeedSetting ? $ecoSpeedSetting['value'] : null;

        return $vehicleEcoSpeed ?: $ecoSpeedSettingValue;
    }

    /**
     * @param Vehicle $vehicle
     * @return int
     */
    public function getExcessiveIdlingValue(Vehicle $vehicle)
    {
        $vehicleExcessiveIdling = $vehicle->getExcessiveIdling();

        if ($vehicleExcessiveIdling) {
            return $vehicleExcessiveIdling;
        }

        return $this->getExcessiveIdlingValueForTeam($vehicle->getTeam());
    }

    /**
     * @param Team $team
     * @return int
     */
    public function getExcessiveIdlingValueForTeam(Team $team)
    {
        if ($this->cache['excessiveIdling'][$team->getId()] ?? null) {
            return $this->cache['excessiveIdling'][$team->getId()];
        } else {
            $excessiveIdlingSetting = $this->getTeamSettingValueByKey($team, Setting::EXCESSIVE_IDLING);
            $value = $excessiveIdlingSetting ? $excessiveIdlingSetting['value'] : Setting::EXCESSIVE_IDLING_VALUE['value'];
            $this->cache['excessiveIdling'][$team->getId()] = $value;

            return $value;
        }
    }

    /**
     * @param Setting $setting
     * @return BaseSettingMapper
     * @throws \Exception
     */
    public function validateSettingValue(Setting $setting): BaseSettingMapper
    {
        $settingMapper = SettingMapperFactory::make($setting, $this->translator);
        $errors = $settingMapper->validate();
        $settingMapper->checkErrors($errors);

        return $settingMapper;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getCommonNameOfSetting(string $name): string
    {
        switch ($name) {
            case in_array($name, Setting::MAP_PROVIDER_IDS):
                $name = Setting::MAP_PROVIDER;
                break;
            case in_array($name, Setting::MAP_API_OPTIONS_IDS):
                $name = Setting::MAP_API_OPTIONS;
                break;
        }

        return $name;
    }

    /**
     * @param string $name
     * @param array $setting
     * @return array
     */
    public static function mapSettingByNameAndSubName(string $name, array $setting): array
    {
        switch ($name) {
            case Setting::MAP_PROVIDER:
                $setting = MapProviderSettingMapper::mapFromArray($setting, $name);
                break;
            case in_array($name, Setting::MAP_API_OPTIONS_IDS):
                $setting = MapApiSettingMapper::mapFromArray($setting, $name);
                break;
        }

        return $setting;
    }

    /**
     * @param User $user
     * @param $data
     * @throws \Doctrine\ORM\ORMException
     */
    public function setUserSetting(User $user, $data)
    {
        foreach ($data as $item) {
            if ($item['name'] === Setting::LOGIN_WITH_ID && $user->isInClientTeam()) {
                throw new AccessDeniedHttpException();
            }

            $setting = $this->em->getRepository(Setting::class)->findOneBy(
                [
                    'team' => $user->getTeam(),
                    'role' => $user->getRole(),
                    'user' => $user,
                    'name' => $item['name']
                ]
            );
            $settingData = [
                'name' => $item['name'],
                'value' => $item['value'],
                'role' => $user->getRole(),
                'team' => $user->getTeam(),
                'user' => $user
            ];

            if (!$setting) {
                $setting = new Setting($settingData);
                $this->em->persist($setting);
            } else {
                $setting->setAttributes($settingData);
            }

            $this->validateSettingValue($setting);
            $this->em->flush();
        }
    }

    /**
     * @param User $user
     * @param string $key
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection|null
     */
    public function getUserSettingByKey(User $user, string $key)
    {
        return $user->getSettingByName($key);
    }

    public function setLanguageSetting(Team $team, string $value, ?User $user = null, ?Role $role = null): Setting
    {
        $setting = $this->em->getRepository(Setting::class)
            ->getSetting(Setting::LANGUAGE_SETTING, $team, $role, $user);

        $data = [
            'name' => Setting::LANGUAGE_SETTING,
            'value' => $value,
            'role' => $role,
            'team' => $team,
            'user' => $user
        ];

        if (!$setting) {
            $setting = new Setting($data);
            $this->em->persist($setting);
        } else {
            $setting->setAttributes($data);
        }

        $this->em->flush();

        return $setting;
    }
}