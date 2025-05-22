<?php

namespace App\Service\Setting\Mapper;

use App\Entity\Setting;

class MapProviderSettingMapper extends BaseSettingMapper
{
    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        $settingValue = $this->setting->getValue();
        $ids = array_keys(Setting::MAP_PROVIDER_IDS);

        if (!is_int($settingValue) || !in_array($settingValue, $ids)) {
            $errors[sprintf('%d.roleId', $this->setting->getRoleId())] = [
                'wrong_value' => $this->translator
                    ->trans('validation.errors.field.wrong_value_for_name', [
                        '%name%' => $this->setting->getName()
                    ])
            ];
        }

        return $errors;
    }

    /**
     * @param null $name
     * @return array|string|integer
     */
    public function getMappedValue($name = null)
    {
        return Setting::MAP_PROVIDER_IDS[$this->setting->getValue()];
    }

    /**
     * @param array $setting
     * @param null $name
     * @return array
     */
    public static function mapFromArray(array $setting, $name = null)
    {
        if (isset($setting['value'])) {
            $setting['value'] = Setting::MAP_PROVIDER_IDS[$setting['value']];
        }

        return $setting;
    }
}
