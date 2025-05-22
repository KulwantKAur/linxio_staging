<?php

namespace App\Service\Setting\Mapper;

use App\Entity\Route;

class RouteScopeSettingMapper extends BaseSettingMapper
{
    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        $settingValueData = $this->setting->getValue();

        if (!Route::settingScopeExists($settingValueData['value'])) {
            $errors['value'] = ['wrong_value' => $this->translator
                ->trans('validation.errors.field.wrong_value', ['%name%' => $this->setting->getName()])
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
        $settingValueData = $this->setting->getValue();
        $settingValue = $settingValueData['value'];

        return match ($settingValue) {
            Route::SCOPE_UNCATEGORISED => null,
            default => $settingValue
        };
    }
}
