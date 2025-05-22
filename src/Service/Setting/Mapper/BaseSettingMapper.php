<?php

namespace App\Service\Setting\Mapper;

use App\Entity\Setting;
use App\Exceptions\ValidationException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BaseSettingMapper
{
    public $translator;
    public $setting;

    /**
     * BaseFileMapper constructor.
     * @param Setting $setting
     * @param null|TranslatorInterface $translator
     */
    public function __construct(Setting $setting, ?TranslatorInterface $translator = null)
    {
        $this->setting = $setting;
        $this->translator = $translator;
    }
    /**
     * @return array
     */
    public function validate(): array
    {
        return [];
    }

    /**
     * @param null $name
     * @return array|bool|int
     */
    public function getMappedValue($name = null)
    {
        return $this->setting->getValue();
    }

    /**
     * @param array $errors
     * @throws ValidationException
     */
    public function checkErrors(array $errors)
    {
        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $setting
     * @param null $name
     * @return array
     */
    public static function mapFromArray(array $setting, $name = null)
    {
        return $setting;
    }
}
