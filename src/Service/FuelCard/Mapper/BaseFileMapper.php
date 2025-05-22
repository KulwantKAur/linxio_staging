<?php

namespace App\Service\FuelCard\Mapper;

use Carbon\Carbon;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BaseFileMapper
{
    protected TranslatorInterface $translator;
    protected bool $isSearchHeader = true;
    protected bool $isShowTime = true;
    protected bool $isSetDateTime = false;
    protected array $header = [];
    protected array $temporaryData = [];
    protected int $headingSearchDepth = 20;

    public const TRANSLATE_DOMAIN = 'fuelCard';

    public const FIELD_VEHICLE = 'vehicle';
    public const FIELD_FUEL_CARD_NUMBER = 'fuelCardNumber';
    public const FIELD_TRANSACTION_DATE = 'transactionDate';
    public const FIELD_TRANSACTION_TIME = 'transactionTime';
    public const FIELD_PETROL_STATION = 'petrolStation';
    public const FIELD_REFUELED_FUEL_TYPE = 'refueledFuelType';
    public const FIELD_REFUELED = 'refueled';
    public const FIELD_TOTAL = 'total';
    public const FIELD_ODOMETER = 'odometer';
    public const FIELD_CARD_ACCOUNT_NO = 'cardAccountNumber';
    public const FIELD_SITE_ID = 'siteId';
    public const FIELD_PRODUCT_CODE = 'productCode';
    public const FIELD_PUMP_PRICE = 'pumpPrice';

    public const DEFAULT_DATE_FORMAT = 'd/m/Y';
    public const DEFAULT_TIME_FORMAT = 'H:i:s';
    public const CALTEX_DATE_FORMAT = 'd/m/y';
    public const CALTEX_DATE_FORMAT_V2 = 'd-m-y';
    public const MOTORPASS_V2_DATE_FORMAT = 'j/m/Y';
    public const SHELL_CARD_DATE_FORMAT = 'c';
    public const SHELL_V2_DATE_FORMAT = 'd/m/Y H:i';
    public const TRANSACTION_V3_DATE_FORMAT = 'd-m-Y';
    public const FUEL_CARD_V2_DATE_FORMAT = 'm/d/Y';
    public const FUEL_CARD_V2_TIME_FORMAT = 'H:i:s';
    public const CHEVRON_DATE_FORMAT = 'Ymd';
    public const CHEVRON_TIME_FORMAT = 'His';
    public const NSW_DATE_FORMAT = 'd.m.Y';

    /**
     * BaseFileMapper constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Describe file format for load in BD
     */
    abstract public function getInternalMappedFields();


    /**
     * @param array $columns
     */
    public function setHeader(array $columns)
    {
        foreach ($this->getInternalMappedFields() as $key => $value) {
            $indexItem = array_search($value, $columns);

            if ($indexItem === false) {
                continue;
            }
            $this->header[$indexItem] = $key;
        }
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    public function isSearchHeader(): bool
    {
        return $this->isSearchHeader;
    }

    /**
     * @return bool
     */
    public function isShowTime(): bool
    {
        return $this->isShowTime;
    }

    /**
     * @return bool
     */
    public function isSetDateTime(): bool
    {
        return $this->isSetDateTime;
    }

    /**
     * @return int
     */
    public function headingSearchDepth(): int
    {
        return $this->headingSearchDepth;
    }

    /**
     * Special field processing before loading
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data)
    {
        $data = $this->processingCardNumberFields($data);
        $data = $this->checkTransactionDate($data);
        $data = $this->processingNumericFields($data);

        return $data;
    }


    public function checkTransactionDate($data, $format = self::DEFAULT_DATE_FORMAT)
    {
        try {
            $data[self::FIELD_TRANSACTION_DATE] = $this->parseDate($format, $data[self::FIELD_TRANSACTION_DATE]);
        } catch (\Exception) {
            $data[self::FIELD_TRANSACTION_DATE] = false;
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processingNumericFields(array $data)
    {
        $numericFields = [self::FIELD_REFUELED, self::FIELD_TOTAL];

        foreach ($numericFields as $field) {
            if (!empty($field)) {
                $data[$field] = !is_float($field)
                    ? (float) (preg_replace('/[^0-9.]/', '', $data[$field]))
                    : $data[$field];
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processingCardNumberFields(array $data)
    {
        if (!empty($data[self::FIELD_FUEL_CARD_NUMBER])) {
            $data[self::FIELD_FUEL_CARD_NUMBER] = is_float($data[self::FIELD_FUEL_CARD_NUMBER])
                ? number_format($data[self::FIELD_FUEL_CARD_NUMBER], 0, '', '')
                : $data[self::FIELD_FUEL_CARD_NUMBER];

            $data[self::FIELD_FUEL_CARD_NUMBER] =
                preg_replace("/[^A-Za-z0-9\s.-]/", '', $data[self::FIELD_FUEL_CARD_NUMBER]);
        }

        return $data;
    }


    /**
     * @param array $data
     * @return bool
     */
    public function checkSkipLine(array $data)
    {
        return !is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE]))
            || empty($data[self::FIELD_REFUELED])
            || empty($data[self::FIELD_VEHICLE]);
    }

    /**
     * @param string $key
     * @return string
     */
    public function translateField(string $key): string
    {
        return $this->translator->trans($key, [], self::TRANSLATE_DOMAIN);
    }

    /**
     * @param string $format
     * @param $date
     * @return string
     */
    public function parseDate(string $format, $transactionDate): string
    {
        if ($this->isSetDateTime() && $this->isShowTime()) {
            return Carbon::createFromFormat(
                $format,
                $transactionDate
            )->toDateTimeString();
        } else {
            return Carbon::createFromFormat(
                $format,
                $transactionDate
            )->toDateString();
        }
    }

    /**
     * @param array $data
     * @param string $format
     * @return mixed
     */
    public function changedTimeFormat(array $data, string $format = self::DEFAULT_TIME_FORMAT)
    {
        try {
            $data[self::FIELD_TRANSACTION_TIME] = $this->parseTime($format, $data[self::FIELD_TRANSACTION_TIME]);
        } catch (\Exception) {
            $data[self::FIELD_TRANSACTION_TIME] = false;
        }
        return $data[self::FIELD_TRANSACTION_TIME];
    }

    /**
     * @param string $format
     * @param $transactionTime
     * @return string
     */
    public function parseTime(string $format, $transactionTime): string
    {
        return Carbon::createFromFormat(
            $format,
            $transactionTime
        )->toTimeString();
    }
}
