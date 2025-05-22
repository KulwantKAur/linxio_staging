<?php

namespace App\Service\Validation;


use App\Service\BaseService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationService extends BaseService
{
    protected $translator;

    public const GREATER_THAN = 'gt';
    public const LESS_THAN = 'lt';

    /**
     * ValidationService constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param array $fields
     * @param string $fieldName
     * @param array $errors
     * @param string $condition
     * @return array
     */
    public function validateDate(array $fields, string $fieldName, array $errors, string $condition = null)
    {
        $date = null;
        try {
            $date =  self::parseDateToUTC($fields[$fieldName]);

            if ($condition === self::GREATER_THAN && (Carbon::now('UTC')->getTimestamp() > $date->getTimestamp())) {
                $errors[$fieldName]['wrong_value'] = $this->translator->trans('validation.errors.field.wrong_value');
            }

            if ($condition === self::LESS_THAN && (Carbon::now('UTC')->getTimestamp() < $date->getTimestamp())) {
                $errors[$fieldName]['wrong_value'] = $this->translator->trans('validation.errors.field.wrong_value');
            }

        } catch (\Exception $ex) {
            $errors[$fieldName]['wrong_format'] = $this->translator->trans('validation.errors.field.wrong_format');
        }

        return $errors;
    }

    /**
     * @param array $fields
     * @param string $fieldName
     * @param array $errors
     * @param string|null $format
     * @return array
     */
    public function validateTime(array $fields, string $fieldName, array $errors, string $format = 'H:i:s')
    {
        $time = null;

        try {
            $time = Carbon::createFromFormat($format, $fields[$fieldName])->toTimeString();
        } catch (\Exception $ex) {
            $errors[$fieldName]['wrong_format'] = $this->translator->trans('validation.errors.field.wrong_format');
        }
        return $errors;
    }
}
