<?php

namespace App\Service\Setting\Factory;

use App\Entity\Setting;
use App\Service\Setting\Mapper\BaseSettingMapper;
use App\Service\Setting\Mapper\DefaultSettingMapper;
use App\Service\Setting\Mapper\DriverAutoLogoutSettingMapper;
use App\Service\Setting\Mapper\MapApiSettingMapper;
use App\Service\Setting\Mapper\MapProviderSettingMapper;
use App\Service\Setting\Mapper\RouteScopeSettingMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingMapperFactory
 */
class SettingMapperFactory
{
    /**
     * @param Setting $setting
     * @param null|TranslatorInterface $translator
     * @return BaseSettingMapper
     */
    public static function make(Setting $setting, ?TranslatorInterface $translator = null): BaseSettingMapper
    {
        switch ($setting->getName()) {
            case Setting::MAP_API_OPTIONS:
                return new MapApiSettingMapper($setting, $translator);
            case Setting::MAP_PROVIDER:
                return new MapProviderSettingMapper($setting, $translator);
            case Setting::DRIVER_AUTO_LOGOUT_BY_VEHICLE:
            case Setting::DRIVER_AUTO_LOGOUT_BY_APP:
                return new DriverAutoLogoutSettingMapper($setting, $translator);
            case Setting::ROUTE_SCOPE:
                return new RouteScopeSettingMapper($setting, $translator);
            default:
                return new DefaultSettingMapper($setting, $translator);
        }
    }
}
