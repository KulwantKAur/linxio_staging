<?php

namespace App\Fixtures\Settings;


use App\Entity\Notification\TemplateSet;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Theme;
use App\Entity\TimeZone;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitSettingsFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public const SETTINGS = [
        [
            'role' => [Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN],
            'name' => Setting::OTP_SETTING,
            'value' => Setting::ENABLED
        ],
        ['role' => [Role::ROLE_ADMIN, Team::TEAM_ADMIN], 'name' => Setting::OTP_SETTING, 'value' => Setting::ENABLED],
        [
            'role' => [Role::ROLE_SALES_REP, Team::TEAM_ADMIN],
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED
        ],
        [
            'role' => [Role::ROLE_ACCOUNT_MANAGER, Team::TEAM_ADMIN],
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED
        ],
        [
            'role' => [Role::ROLE_INSTALLER, Team::TEAM_ADMIN],
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED
        ],
        [
            'role' => [Role::ROLE_SUPPORT, Team::TEAM_ADMIN],
            'name' => Setting::OTP_SETTING,
            'value' => Setting::DISABLED
        ],
        ['role' => null, 'name' => Setting::SMS_SETTING, 'value' => Setting::ENABLED],
        ['role' => null, 'name' => Setting::EMAIL_SETTING, 'value' => Setting::ENABLED],
        ['role' => null, 'name' => Setting::IN_APP_SETTING, 'value' => Setting::ENABLED],
        ['role' => null, 'name' => Setting::THEME_SETTING, 'value' => Setting::DISABLED],
        ['role' => null, 'name' => Setting::LANGUAGE_SETTING, 'value' => Setting::LANGUAGE_SETTING_DEFAULT_VALUE],
        ['role' => null, 'name' => Setting::NOTIFICATION_TEMPLATE_SETTING, 'value' => null],
        ['role' => [Role::ROLE_SALES_REP, Team::TEAM_ADMIN], 'name' => Setting::TIMEZONE_SETTING, 'value' => null],
        ['role' => null, 'name' => Setting::ECO_SPEED, 'value' => Setting::ECO_SPEED_VALUE],
        ['role' => null, 'name' => Setting::EXCESSIVE_IDLING, 'value' => Setting::EXCESSIVE_IDLING_VALUE],
        ['role' => null, 'name' => Setting::VEHICLE_ENGINE_OFF, 'value' => Setting::VEHICLE_ENGINE_OFF_VALUE],
        ['role' => null, 'name' => Setting::DEVICE_VOLTAGE, 'value' => Setting::DEVICE_VOLTAGE_VALUE],
        ['role' => null, 'name' => Setting::OVERSPEEDING_DURATION, 'value' => Setting::OVERSPEEDING_DURATION_VALUE],
        ['role' => null, 'name' => Setting::LONG_STANDING_DURATION, 'value' => Setting::LONG_STANDING_DURATION_VALUE],
        [
            'role' => null,
            'name' => Setting::INSPECTION_FORM_PERIOD,
            'value' => Setting::INSPECTION_FORM_PERIOD_EVERY_TIME
        ],
        ['role' => null, 'name' => Setting::LOGIN_WITH_ID, 'value' => Setting::DISABLED],
        ['role' => null, 'name' => Setting::TRACKING_LINK, 'value' => Setting::TRACKING_LINK_VALUE],
        [
            'role' => null,
            'name' => Setting::TRACKING_LINK_DEFAULT_MESSAGE,
            'value' => Setting::TRACKING_LINK_DEFAULT_MESSAGE_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::DATE_FORMAT,
            'value' => Setting::DATE_FORMAT_VALUE
        ],
        ['role' => null, 'name' => Setting::INTEGRATIONS, 'value' => Setting::INTEGRATIONS_DEFAULT_VALUE],
        ['role' => null, 'name' => Setting::DIGITAL_FORM, 'value' => Setting::DIGITAL_FORM_DEFAULT_VALUE],
        [
            'role' => null,
            'name' => Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE,
            'value' => Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE_DEFAULT_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP,
            'value' => Setting::HIDE_FORMS_ON_THE_DASHBOARDS_IN_MOBILE_APP_DEFAULT_VALUE
        ],
        ['role' => null, 'name' => Setting::ASSET_SETTING, 'value' => Setting::DISABLED],
        [
            'role' => null,
            'name' => Setting::DISALLOW_DRIVER_LOGIN_WEBAPP,
            'value' => Setting::DISALLOW_DRIVER_LOGIN_WEBAPP_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::VEHICLE_MAP_TITLE_INSTEAD_OF_REGNO,
            'value' => Setting::VEHICLE_MAP_TITLE_INSTEAD_OF_REGNO_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::VEHICLE_MAP_FIELD_WITH_ICON,
            'value' => Setting::VEHICLE_MAP_FIELD_WITH_ICON_VALUE
        ],
        ['role' => null, 'name' => Setting::GPS_STATUS_DURATION, 'value' => Setting::GPS_STATUS_DURATION_VALUE],
        ['role' => null, 'name' => Setting::END_TRIP, 'value' => Setting::END_TRIP_VALUE],
        ['role' => null, 'name' => Setting::IGNORE_STOPS, 'value' => Setting::IGNORE_STOPS_VALUE],
        ['role' => null, 'name' => Setting::IGNORE_MOVEMENT, 'value' => Setting::IGNORE_MOVEMENT_VALUE],
        ['role' => null, 'name' => Setting::IDLING, 'value' => Setting::IDLING_VALUE],
        ['role' => null, 'name' => Setting::MESSENGER, 'value' => Setting::MESSENGER_DEFAULT_VALUE],
        [
            'role' => null,
            'name' => Setting::MESSENGER_UPLOAD_FILE_LIMIT,
            'value' => Setting::MESSENGER_UPLOAD_FILE_LIMIT_DEFAULT_VALUE
        ],
        ['role' => null, 'name' => Setting::BILLING, 'value' => Setting::BILLING_VALUE],
        ['role' => null, 'name' => Setting::REPORTS, 'value' => Setting::REPORTS_DEFAULT_VALUE],
        [
            'role' => null,
            'name' => Setting::DRIVER_AUTO_LOGOUT_OPTION,
            'value' => Setting::DRIVER_AUTO_LOGOUT_OPTION_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::DRIVER_AUTO_LOGOUT_BY_APP,
            'value' => Setting::DRIVER_AUTO_LOGOUT_BY_APP_VALUE
        ],
        [
            'role' => null,
            'name' => Setting::DRIVER_AUTO_LOGOUT_BY_VEHICLE,
            'value' => Setting::DRIVER_AUTO_LOGOUT_BY_VEHICLE_VALUE
        ],
        ['role' => null, 'name' => Setting::ROUTE_OPTIMIZATION, 'value' => Setting::ROUTE_OPTIMIZATION_VALUE],
        ['role' => null, 'name' => Setting::ROUTE_SCOPE, 'value' => Setting::ROUTE_SCOPE_DEFAULT_VALUE],
        ['role' => null, 'name' => Setting::TIME_12H, 'value' => Setting::TIME_12H_VALUE],
        ['role' => null, 'name' => Setting::USER_AUTO_LOGOUT_OPTION, 'value' => Setting::USER_AUTO_LOGOUT_OPTION_VALUE],
        ['role' => null, 'name' => Setting::USER_LOCAL_AUTH_OPTION, 'value' => Setting::USER_LOCAL_AUTH_OPTION_VALUE],
        ['role' => null, 'name' => Setting::USER_TERMS_ACCEPTANCE, 'value' => Setting::USER_TERMS_ACCEPTANCE_VALUE],
        ['role' => null, 'name' => Setting::TRIP_CODE, 'value' => Setting::TRIP_CODE_VALUE],
        ['role' => null, 'name' => Setting::BILLABLE_ADDONS, 'value' => Setting::BILLABLE_ADDONS_VALUE],
        ['role' => null, 'name' => Setting::CAMERAS, 'value' => Setting::CAMERAS_VALUE],
        ['role' => null, 'name' => Setting::BIOMETRIC_LOGIN, 'value' => Setting::BIOMETRIC_LOGIN_VALUE],
        [
            'role' => null,
            'name' => Setting::DRIVER_VEHICLES_ACCESS_TYPE,
            'value' => Setting::DRIVER_VEHICLES_ACCESS_TYPE_DEFAULT_VALUE
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $adminTeam = $manager->getRepository(Team::class)->findOneBy(
            [
                'type' => Team::TEAM_ADMIN
            ]
        );
        foreach (self::SETTINGS as $settingData) {
            if ($settingData['name'] === Setting::THEME_SETTING) {
                $settingData['value'] = $manager->getRepository(Theme::class)
                    ->findOneBy(['alias' => Theme::DEFAULT_THEME_ALIAS])->getId();
            }
            if ($settingData['name'] === Setting::NOTIFICATION_TEMPLATE_SETTING) {
                $settingData['value'] = $manager->getRepository(TemplateSet::class)
                    ->findOneBy(['name' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME])->getId();
            }
            if ($settingData['name'] === Setting::TIMEZONE_SETTING) {
                $settingData['value'] = $manager->getRepository(TimeZone::class)
                    ->findOneBy(['name' => TimeZone::DEFAULT_TIMEZONE['name']])->getId();
            }

            if (!isset($settingData['team']) && !isset($settingData['role'])) {
                $teams = $manager->getRepository(Team::class)->findAll();
                foreach ($teams as $team) {
                    $settingIsset = $manager->getRepository(Setting::class)->findOneBy(
                        [
                            'team' => $team,
                            'name' => $settingData['name'],
                            'role' => null
                        ]
                    );
                    if (!$settingIsset) {
                        $setting = new Setting($settingData);
                        $setting->setTeam($team);
                        $manager->persist($setting);
                    }
                }
            } else {
                $team = (!empty($settingData['team']))
                    ? $manager->getRepository(Team::class)->findOneBy(
                        ['type' => $settingData['team']],
                        ['id' => 'DESC']
                    )
                    : $adminTeam;
                $roleId = (!empty($settingData['role']))
                    ? $this->getReference(implode('_', $settingData['role']))->getId()
                    : $settingData['role'];
                if (!$manager->getRepository(Role::class)->findBy(
                    [
                        'id' => $roleId,
                        'team' => $team
                    ]
                )) {
                    $settingData['role'] = $manager->getRepository(Role::class)->findOneBy(['id' => $roleId]);
                    $settingIsset = $manager->getRepository(Setting::class)->findOneBy(
                        [
                            'team' => $team,
                            'name' => $settingData['name'],
                            'role' => $settingData['role']
                        ]
                    );
                    if (!$settingIsset) {
                        $setting = new Setting($settingData);
                        $setting->setTeam($team);
                        $manager->persist($setting);
                    }
                }
            }
        }
        $manager->flush();
    }
}
