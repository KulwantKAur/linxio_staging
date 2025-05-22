<?php

namespace App\Service\Setting\Mapper;

use App\Entity\Setting;

class MapApiSettingMapper extends BaseSettingMapper
{
    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        $settingValue = $this->setting->getValue();
        $ids = array_keys(Setting::MAP_API_OPTIONS_IDS);

        if (!is_array($settingValue)) {
            $errors[sprintf('%d.roleId', $this->setting->getRoleId())] = [
                'wrong_value' => $this->translator
                    ->trans('validation.errors.field.wrong_value_for_name', [
                        '%name%' => $this->setting->getName()
                    ])
            ];
        }

        if (!count($errors) && is_array($settingValue)) {
            foreach ($settingValue as $value) {
                if (!in_array($value, $ids)) {
                    $errors[sprintf('%d.roleId', $this->setting->getRoleId())] = [
                        'wrong_value' => $this->translator
                            ->trans('validation.errors.field.wrong_value_for_name', [
                                '%name%' => $this->setting->getName(),
                                '%value%' => $value
                            ])
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * @param null $name
     * @return array|string|integer
     */
    public function getMappedValue($name = null)
    {
        $settingValue = is_array($this->setting->getValue()) ? $this->setting->getValue() : [$this->setting->getValue()];
        $settingIds = array_fill_keys($settingValue, 0);
        $existingKeys = array_intersect_key(Setting::MAP_API_OPTIONS_IDS, $settingIds);
        $notExistingKeys = array_diff_key(Setting::MAP_API_OPTIONS_IDS, $settingIds);

        $existingValues = array_fill_keys(array_values($existingKeys), true);
        $notExistingValues = array_fill_keys(array_values($notExistingKeys), false);

        $value = array_merge($existingValues, $notExistingValues);

        if ($name) {
            $value = $value[$name];
        }

        return $value;
    }

    /**
     * @param array $setting
     * @param null $name
     * @return array
     */
    public static function mapFromArray(array $setting, $name = null)
    {
        if (isset($setting['value'])) {
            $settingValue = is_array($setting['value']) ? $setting['value'] : [$setting['value']];
            $settingIds = array_fill_keys($settingValue, 0);
            $existingKeys = array_intersect_key(Setting::MAP_API_OPTIONS_IDS, $settingIds);
            $notExistingKeys = array_diff_key(Setting::MAP_API_OPTIONS_IDS, $settingIds);
            $existingValues = array_fill_keys(array_values($existingKeys), true);
            $notExistingValues = array_fill_keys(array_values($notExistingKeys), false);
            $setting['value'] = array_merge($existingValues, $notExistingValues);

            if ($name) {
                $setting['value'] = $setting['value'][$name];
            }
        }

        return $setting;
    }
}
