<?php

namespace App\Service\Setting\Mapper;

class DriverAutoLogoutSettingMapper extends BaseSettingMapper
{
    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        $settingValue = $this->setting->getValue();

        if (intval($settingValue) < 0) {
            $errors['value'] = ['wrong_value' => $this->translator
                ->trans('validation.errors.field.wrong_value', ['%name%' => $this->setting->getName()])
            ];
        }

        return $errors;
    }
}
