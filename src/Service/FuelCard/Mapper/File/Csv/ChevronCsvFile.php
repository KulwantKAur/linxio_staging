<?php

declare(strict_types=1);

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class ChevronCsvFile extends BaseFileMapper
{
    public const TRANSACTION_TYPE_SAVE = [
        'CV Manual Purchase',
        'CV Purchase',
    ];

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.chevron.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.chevron.transactionTime'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.chevron.fuelCardNumber'),
            self::FIELD_VEHICLE => $this->translateField('import.chevron.vehicle'),
            self::FIELD_ODOMETER => $this->translateField('import.chevron.odometer'),
            self::FIELD_CARD_ACCOUNT_NO => $this->translateField('import.chevron.cardAccountNo'),
            self::FIELD_SITE_ID => $this->translateField('import.chevron.siteId'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.chevron.refueledFuelType'),
            self::FIELD_PRODUCT_CODE => $this->translateField('import.chevron.productCode'),
            self::FIELD_REFUELED => $this->translateField('import.chevron.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.chevron.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.chevron.petrolStation'),
            self::FIELD_PUMP_PRICE => $this->translateField('import.chevron.pumpPrice'),
            'TransactionType' => $this->translateField('import.chevron.onlyReadFields.transactionType'),
            'CustomerName' => $this->translateField('import.chevron.onlyReadFields.customerName'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        if (!empty($data[self::FIELD_TRANSACTION_TIME])) {
            $data[self::FIELD_TRANSACTION_TIME] = $this->modifyTime($data[self::FIELD_TRANSACTION_TIME]);
            $data[self::FIELD_TRANSACTION_TIME] = $this->changedTimeFormat($data, self::CHEVRON_TIME_FORMAT);
        } else {
            unset($data[self::FIELD_TRANSACTION_TIME]);
        }

        $data = $this->checkTransactionDate($data, self::CHEVRON_DATE_FORMAT);
        $data = $this->processingCardNumberFields($data);
        return $this->processingNumericFields($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function checkSkipLine(array $data): bool
    {
        return (is_string($data[self::FIELD_TRANSACTION_DATE])
                && !is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE])))
            || empty($data[self::FIELD_REFUELED])
//            || empty($data[self::FIELD_VEHICLE])
            || empty($data['CustomerName'])
            || (!in_array($data['TransactionType'], self::TRANSACTION_TYPE_SAVE))
            ;
    }

    /**
     * @param $time
     * @return mixed
     */
    public function modifyTime($time): mixed
    {
        if (strlen((string)$time) < 6) {
            return sprintf('%06d', $time);
        }
        return $time;
    }
}
